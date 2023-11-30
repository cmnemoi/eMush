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
 * Class implementing the shoot action.
 */
class Shoot extends AttemptAction
{
    protected string $name = ActionEnum::SHOOT;

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

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Player;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PreMush(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::PRE_MUSH_AGGRESSIVE]));
        $metadata->addConstraint(new HasEquipment([
            'reach' => ReachEnum::INVENTORY,
            'equipments' => [ItemEnum::BLASTER],
            'contains' => true,
            'checkIfOperational' => true,
            'all' => false,
            'target' => HasEquipment::PLAYER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::UNLOADED_WEAPON,
        ]));
        $metadata->addConstraint(new HasEquipment([
            'reach' => ReachEnum::SHELVE,
            'equipments' => [ItemEnum::BLASTER],
            'contains' => false,
            'target' => HasEquipment::PLAYER,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
    }

    // Special checkResult for Shoot action waiting for a refactor
    protected function checkResult(): ActionResult
    {
        $blaster = $this->getPlayerBlaster();

        $success = $this->randomService->isSuccessful($this->getSuccessRate());

        if ($success) {
            if ($this->rollCriticalChances($blaster->getOneShotRate())) {
                return new OneShot();
            }
            if ($this->rollCriticalChances($blaster->getCriticalSuccessRate())) {
                return new CriticalSuccess();
            }

            return new Success();
        } else {
            if ($this->rollCriticalChances($blaster->getCriticalFailRate())) {
                return new CriticalFail();
            }

            return new Fail();
        }
    }

    protected function applyEffect(ActionResult $result): void
    {
        $player = $this->player;
        /** @var Player $target */
        $target = $this->target;

        $blaster = $this->getPlayerBlaster();

        if ($result instanceof Success) {
            $damage = intval($this->randomService->getSingleRandomElementFromProbaCollection($blaster->getBaseDamageRange()));
            $damageEvent = $this->createDamageEvent($damage, $target);

            if ($result instanceof OneShot) {
                $reasons = $this->getAction()->getActionTags();
                $reasons[] = EndCauseEnum::BLED;
                $reasons[] = ActionOutputEnum::ONE_SHOT;
                $deathEvent = new PlayerEvent(
                    $target,
                    $reasons,
                    new \DateTime()
                );

                $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);

                return;
            } elseif ($result instanceof CriticalSuccess) {
                $this->diseaseCauseService->handleDiseaseForCause(DiseaseCauseEnum::CRITICAL_SUCCESS_KNIFE, $target);
                $damageEvent->addTag(ActionOutputEnum::CRITICAL_SUCCESS);
            }

            $this->eventService->callEvent($damageEvent, VariableEventInterface::CHANGE_VARIABLE);
        } else {
            if ($result instanceof CriticalFail) {
                $this->diseaseCauseService->handleDiseaseForCause(DiseaseCauseEnum::CRITICAL_FAIL_KNIFE, $player);
            }
        }
    }

    private function getPlayerBlaster(): Weapon
    {
        /** @var GameItem $blasterItem */
        $blasterItem = $this->player->getEquipments()->filter(
            fn (GameItem $gameItem) => $gameItem->getName() === ItemEnum::BLASTER && $gameItem->isOperational()
        )->first();

        if (!$blasterItem instanceof GameItem) {
            throw new \Exception("Shoot action : {$this->player->getLogName()} should have a blaster");
        }

        /** @var Weapon $blasterWeapon */
        $blasterWeapon = $blasterItem->getEquipment()->getMechanics()->first();
        if (!$blasterWeapon instanceof Weapon) {
            throw new \Exception('Shoot action : Blaster should have a weapon mechanic');
        }

        return $blasterWeapon;
    }

    private function rollCriticalChances(int $percentage): bool
    {
        $criticalRollEvent = new ActionVariableEvent(
            $this->action,
            ActionVariableEnum::PERCENTAGE_CRITICAL,
            $percentage,
            $this->player,
            $this->target
        );

        /** @var ActionVariableEvent $criticalRollEvent */
        $criticalRollEvent = $this->eventService->computeEventModifications($criticalRollEvent, ActionVariableEvent::ROLL_ACTION_PERCENTAGE);

        return $this->randomService->isSuccessful($criticalRollEvent->getRoundedQuantity());
    }

    private function createDamageEvent(int $damage, Player $target): PlayerVariableEvent
    {
        return new PlayerVariableEvent(
            $target,
            PlayerVariableEnum::HEALTH_POINT,
            -$damage,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );
    }
}
