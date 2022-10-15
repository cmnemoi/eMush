<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\CriticalFail;
use Mush\Action\ActionResult\CriticalSuccess;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\OneShot;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\Reach;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the attack Action.
 */
class Attack extends AttemptAction
{
    protected string $name = ActionEnum::ATTACK;

    private ModifierServiceInterface $modifierService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    protected RandomServiceInterface $randomService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        ModifierServiceInterface $modifierService,
        PlayerDiseaseServiceInterface $playerDiseaseService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator,
            $randomService
        );

        $this->modifierService = $modifierService;
        $this->playerDiseaseService = $playerDiseaseService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Player;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasEquipment([
            'reach' => ReachEnum::INVENTORY,
            'equipments' => [ItemEnum::KNIFE],
            'contains' => true,
            'checkIfOperational' => true,
            'target' => HasEquipment::PLAYER,
            'groups' => ['visibility'],
        ]));
    }

    protected function checkResult(): ActionResult
    {
        $player = $this->player;

        $knifeItem = $this->getPlayerKnife();
        if ($knifeItem == null) {
            throw new \Exception("Attack action : {$player->getLogName()} should have a knife");
        }

        /** @var Weapon $knifeWeapon */
        $knifeWeapon = $knifeItem->getMechanics()->first();

        if (!$knifeWeapon instanceof Weapon) {
            throw new \Exception('Attack action : Knife should have a weapon mechanic');
        }

        $result = parent::checkResult();

        if ($result instanceof Success) {
            if ($this->isOneShot($player, $knifeWeapon)) {
                return new OneShot();
            }

            if ($this->isCriticalSuccess($player, $knifeWeapon)) {
                return new CriticalSuccess();
            }

            return new Success();
        } else {
            if ($this->isCriticalFail($player, $knifeWeapon)) {
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

        $knifeItem = $this->getPlayerKnife();
        if ($knifeItem == null) {
            throw new \Exception("Attack action : {$player->getLogName()} should have a knife");
        }

        /** @var Weapon $knifeWeapon */
        $knifeWeapon = $knifeItem->getMechanics()->first();
        if (!$knifeWeapon instanceof Weapon) {
            throw new \Exception('Attack action : Knife should have a weapon mechanic');
        }

        if ($result instanceof Success) {
            if ($result instanceof OneShot) {
                $deathEvent = new PlayerEvent(
                    $target,
                    EndCauseEnum::BLED,
                    new \DateTime()
                );

                $this->eventDispatcher->dispatch($deathEvent, PlayerEvent::DEATH_PLAYER);

                return;
            }

            $damage = intval($this->randomService->getSingleRandomElementFromProbaArray($knifeWeapon->getBaseDamageRange()));

            if ($result instanceof CriticalSuccess) {
                $this->playerDiseaseService->handleDiseaseForCause(DiseaseCauseEnum::CRITICAL_SUCCESS_KNIFE, $target);
            } else {
                // handle modifiers on damage : armor, hard boiled, etc
                $damage = $this->modifierService->getEventModifiedValue(
                    $target,
                    [ModifierScopeEnum::INJURY],
                    PlayerVariableEnum::HEALTH_POINT,
                    $damage,
                    $this->getActionName(),
                    new \DateTime()
                );
            }

            $this->inflictDamage($damage, $target);
        } else {
            if ($result instanceof CriticalFail) {
                $this->playerDiseaseService->handleDiseaseForCause(DiseaseCauseEnum::CRITICAL_FAIL_KNIFE, $player);
            }
        }
    }

    private function getPlayerKnife(): ?EquipmentConfig
    {
        return $this->player->getEquipments()->filter(
            fn (GameItem $gameItem) => $gameItem->getName() === ItemEnum::KNIFE && $gameItem->isOperational()
        )->first()->getEquipment();
    }

    private function isCriticalFail(Player $player, Weapon $knife): bool
    {
        $criticalFailRate = $this->modifierService->getEventModifiedValue(
            $player,
            [ActionTypeEnum::ACTION_ATTACK],
            ModifierTargetEnum::CRITICAL_PERCENTAGE,
            $knife->getCriticalFailRate(),
            $this->getActionName(),
            new \DateTime()
        );

        return $this->randomService->isSuccessful($criticalFailRate);
    }

    private function isCriticalSuccess(Player $player, Weapon $knife): bool
    {
        $criticalSuccessRate = $this->modifierService->getEventModifiedValue(
            $player,
            [ActionTypeEnum::ACTION_ATTACK],
            ModifierTargetEnum::CRITICAL_PERCENTAGE,
            $knife->getCriticalSucessRate(),
            $this->getActionName(),
            new \DateTime()
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
            $this->getActionName(),
            new \DateTime()
        );

        return $this->randomService->isSuccessful($oneShotRate);
    }

    private function inflictDamage(int $damage, Player $target): void
    {
        $damageEvent = new PlayerVariableEvent(
            $target,
            PlayerVariableEnum::HEALTH_POINT,
            -$damage,
            $this->getActionName(),
            new \DateTime()
        );

        $this->eventDispatcher->dispatch(
            $damageEvent,
            AbstractQuantityEvent::CHANGE_VARIABLE
        );
    }
}
