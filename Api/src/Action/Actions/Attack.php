<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\CriticalFail;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\OneShot;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\PreMush;
use Mush\Action\Validator\Reach;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the attack ActionConfig.
 */
class Attack extends AttemptAction
{
    protected ActionEnum $name = ActionEnum::ATTACK;
    protected RandomServiceInterface $randomService;

    private DiseaseCauseServiceInterface $diseaseCauseService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        DiseaseCauseServiceInterface $diseaseCauseService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator,
            $randomService
        );

        $this->diseaseCauseService = $diseaseCauseService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PreMush(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::PRE_MUSH_AGGRESSIVE]));
        $metadata->addConstraint(new HasEquipment([
            'reach' => ReachEnum::INVENTORY,
            'equipments' => [ItemEnum::KNIFE],
            'contains' => true,
            'checkIfOperational' => true,
            'target' => HasEquipment::PLAYER,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new PlaceType(['groups' => ['visibility'], 'type' => 'room']));
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Player;
    }

    // Special checkResult for Attack action waiting for a refactor
    protected function checkResult(): ActionResult
    {
        $knife = $this->getPlayerKnife();

        $success = $this->randomService->isSuccessful($this->getSuccessRate());

        if ($success) {
            if ($this->rollCriticalChances($knife->getOneShotRate())) {
                return new OneShot();
            }
            if ($this->rollCriticalChances($knife->getCriticalSuccessRate())) {
                return new CriticalSuccess();
            }

            return new Success();
        }
        if ($this->rollCriticalChances($knife->getCriticalFailRate())) {
            return new CriticalFail();
        }

        return new Fail();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $player = $this->player;

        /** @var Player $target */
        $target = $this->target;

        $knife = $this->getPlayerKnife();

        if ($result instanceof Success) {
            $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection($knife->getBaseDamageRange());
            $damageEvent = $this->createDamageEvent($damage, $target, $player);

            if ($result instanceof OneShot) {
                $tags = $this->getActionConfig()->getActionTags();
                $tags[] = EndCauseEnum::BLED;
                $tags[] = ActionOutputEnum::ONE_SHOT;
                $deathEvent = new PlayerEvent(
                    $target,
                    $tags,
                    new \DateTime()
                );

                $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);

                return;
            }
            if ($result instanceof CriticalSuccess) {
                $this->diseaseCauseService->handleDiseaseForCause(DiseaseCauseEnum::CRITICAL_SUCCESS_KNIFE, $target);
                $damageEvent->addTag(ActionOutputEnum::CRITICAL_SUCCESS);
            }

            // handle modifiers on damage : armor, hard boiled, etc
            $this->eventService->callEvent($damageEvent, VariableEventInterface::CHANGE_VARIABLE);
        } else {
            if ($result instanceof CriticalFail) {
                $this->diseaseCauseService->handleDiseaseForCause(DiseaseCauseEnum::CRITICAL_FAIL_KNIFE, $player);
            }
        }
    }

    private function getPlayerKnife(): Weapon
    {
        /** @var GameItem $knifeItem */
        $knifeItem = $this->player->getEquipments()->filter(
            static fn (GameItem $gameItem) => $gameItem->getName() === ItemEnum::KNIFE && $gameItem->isOperational()
        )->first();

        if (!$knifeItem instanceof GameItem) {
            throw new \Exception("Attack action : {$this->player->getLogName()} should have a knife");
        }

        /** @var Weapon $knifeWeapon */
        $knifeWeapon = $knifeItem->getEquipment()->getMechanics()->first();
        if (!$knifeWeapon instanceof Weapon) {
            throw new \Exception('Attack action : Knife should have a weapon mechanic');
        }

        return $knifeWeapon;
    }

    private function rollCriticalChances(int $percentage): bool
    {
        $criticalRollEvent = new ActionVariableEvent(
            $this->actionConfig,
            $this->actionProvider,
            ActionVariableEnum::PERCENTAGE_CRITICAL,
            $percentage,
            $this->player,
            $this->target
        );

        /** @var ActionVariableEvent $criticalRollEvent */
        $criticalRollEvent = $this->eventService->computeEventModifications($criticalRollEvent, ActionVariableEvent::ROLL_ACTION_PERCENTAGE);

        return $this->randomService->isSuccessful($criticalRollEvent->getRoundedQuantity());
    }

    private function createDamageEvent(int $damage, Player $target, Player $author): PlayerVariableEvent
    {
        $damageEvent = new PlayerVariableEvent(
            $target,
            PlayerVariableEnum::HEALTH_POINT,
            -$damage,
            $this->getActionConfig()->getActionTags(),
            new \DateTime(),
        );
        $damageEvent->setAuthor($author);

        return $damageEvent;
    }
}
