<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\CriticalFail;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\OneShot;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\PreMush;
use Mush\Action\Validator\Reach;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Service\EventModifierServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the attack Action.
 */
class Attack extends AttemptAction
{
    protected string $name = ActionEnum::ATTACK;

    private EventModifierServiceInterface $modifierService;
    private DiseaseCauseServiceInterface $diseaseCauseService;
    protected RandomServiceInterface $randomService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        EventModifierServiceInterface $modifierService,
        DiseaseCauseServiceInterface $diseaseCauseService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator,
            $randomService
        );

        $this->modifierService = $modifierService;
        $this->diseaseCauseService = $diseaseCauseService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Player;
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
    }

    // Special checkResult for Attack action waiting for a refactor
    protected function checkResult(): ActionResult
    {
        $player = $this->player;

        $knife = $this->getPlayerKnife();

        $success = $this->randomService->isSuccessful($this->getSuccessRate());

        if ($success) {
            if ($this->isOneShot($player, $knife)) {
                return new OneShot();
            }

            return new Success();
        } else {
            if ($this->isCriticalFail($player, $knife)) {
                return new CriticalFail();
            }

            return new Fail();
        }
    }

    protected function applyEffect(ActionResult $result): void
    {
        $player = $this->player;
        /** @var Player $target */
        $target = $this->parameter;

        $knife = $this->getPlayerKnife();

        if ($result instanceof Success) {
            if ($result instanceof OneShot) {
                $reasons = $this->getAction()->getActionTags();
                $reasons[] = EndCauseEnum::BLED;
                $deathEvent = new PlayerEvent(
                    $target,
                    $reasons,
                    new \DateTime()
                );

                $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);

                return;
            }

            $damage = intval($this->randomService->getSingleRandomElementFromProbaArray($knife->getBaseDamageRange()));

            if ($this->isCriticalSuccess($player, $knife)) {
                $this->diseaseCauseService->handleDiseaseForCause(DiseaseCauseEnum::CRITICAL_SUCCESS_KNIFE, $target);
            } else {
                // handle modifiers on damage : armor, hard boiled, etc
                $damage = $this->modifierService->getEventModifiedValue(
                    $target,
                    [ModifierScopeEnum::INJURY],
                    PlayerVariableEnum::HEALTH_POINT,
                    $damage,
                    $this->getAction()->getActionTags(),
                    new \DateTime()
                );
            }

            $this->inflictDamage($damage, $target);
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
            fn (GameItem $gameItem) => $gameItem->getName() === ItemEnum::KNIFE && $gameItem->isOperational()
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

    private function isCriticalFail(Player $player, Weapon $knife): bool
    {
        $criticalFailRate = $this->modifierService->getEventModifiedValue(
            $player,
            [ActionTypeEnum::ACTION_ATTACK],
            ModifierTargetEnum::CRITICAL_PERCENTAGE,
            $knife->getCriticalFailRate(),
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );

        return $this->randomService->isSuccessful($criticalFailRate);
    }

    private function isCriticalSuccess(Player $player, Weapon $knife): bool
    {
        $criticalSuccessRate = $this->modifierService->getEventModifiedValue(
            $player,
            [ActionTypeEnum::ACTION_ATTACK],
            ModifierTargetEnum::CRITICAL_PERCENTAGE,
            $knife->getCriticalSuccessRate(),
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );

        return $this->randomService->isSuccessful($criticalSuccessRate);
    }

    private function isOneShot(Player $player, Weapon $knife): bool
    {
        $oneShotRate = $this->modifierService->getEventModifiedValue(
            $player,
            [ActionTypeEnum::ACTION_ATTACK],
            ModifierTargetEnum::CRITICAL_PERCENTAGE,
            $knife->getOneShotRate(),
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );

        return $this->randomService->isSuccessful($oneShotRate);
    }

    private function inflictDamage(int $damage, Player $target): void
    {
        $damageEvent = new PlayerVariableEvent(
            $target,
            PlayerVariableEnum::HEALTH_POINT,
            -$damage,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );

        $this->eventService->callEvent(
            $damageEvent,
            VariableEventInterface::CHANGE_VARIABLE
        );
    }
}
