<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\CriticalFail;
use Mush\Action\ActionResult\CriticalSuccess;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\PreMush;
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
 * Class implementing the shoot action.
 */
class Shoot extends AttemptAction
{
    protected string $name = ActionEnum::SHOOT;

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
        $metadata->addConstraint(new PreMush(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::PRE_MUSH_AGGRESSIVE]));
        $metadata->addConstraint(new HasEquipment([
            'reach' => ReachEnum::INVENTORY,
            'equipments' => [ItemEnum::BLASTER],
            'contains' => false,
            'checkIfOperational' => false,
            'all' => false,
            'target' => HasEquipment::PLAYER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::UNLOADED_WEAPON,
        ]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var Player $player */
        $player = $this->player;
        /** @var Player $target */
        $target = $this->parameter;

        /** @var EquipmentConfig $blasterItem */
        $blasterItem = $this->getPlayerBlaster();
        if ($blasterItem == null) {
            throw new \Exception("Attack action : {$player->getLogName()} should have a blaster");
        }

        /** @var Weapon $blasterWeapon */
        $blasterWeapon = $blasterItem->getMechanics()->first();

        if (!$blasterWeapon instanceof Weapon) {
            throw new \Exception('Attack action : Blaster should have a weapon mechanic');
        }

        $result = $this->makeAttempt();

        if ($result instanceof Success) {
            $isAOneShot = $this->handleOneShot($player, $target, $blasterWeapon);
            if ($isAOneShot) {
                return new CriticalSuccess();
            }

            $damage = intval($this->randomService->getSingleRandomElementFromProbaArray($blasterWeapon->getBaseDamageRange()));

            $isACriticalSuccess = $this->handleCriticalSuccess($player, $target, $blasterWeapon);
            // handle modifiers on damage : armor, hard boiled, etc
            if (!$isACriticalSuccess) {
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

            return new Success();
        } else {
            $isACriticalFail = $this->handleCriticalFail($player, $blasterWeapon);
            if ($isACriticalFail) {
                return new CriticalFail();
            }

            return new Fail();
        }
    }

    private function getPlayerBlaster(): ?EquipmentConfig
    {
        return $this->player->getEquipments()->filter(
            fn (GameItem $gameItem) => $gameItem->getName() === ItemEnum::BLASTER && $gameItem->isOperational()
        )->first()->getEquipment();
    }

    private function handleCriticalFail(Player $player, Weapon $blaster): bool
    {
        $criticalFailRate = $this->modifierService->getEventModifiedValue(
            $player,
            [ActionTypeEnum::ACTION_SHOOT],
            ModifierTargetEnum::CRITICAL_PERCENTAGE,
            $blaster->getCriticalFailRate(),
            $this->getActionName(),
            new \DateTime()
        );

        if ($this->randomService->isSuccessful($criticalFailRate)) {
            $this->playerDiseaseService->handleDiseaseForCause(DiseaseCauseEnum::CRITICAL_FAIL_BLASTER, $player);

            return true;
        }

        return false;
    }

    private function handleCriticalSuccess(Player $player, Player $target, Weapon $blaster): bool
    {
        $criticalSuccessRate = $this->modifierService->getEventModifiedValue(
            $player,
            [ActionTypeEnum::ACTION_SHOOT],
            ModifierTargetEnum::CRITICAL_PERCENTAGE,
            $blaster->getCriticalSucessRate(),
            $this->getActionName(),
            new \DateTime()
        );

        if ($this->randomService->isSuccessful($criticalSuccessRate)) {
            $this->playerDiseaseService->handleDiseaseForCause(DiseaseCauseEnum::CRITICAL_SUCCESS_BLASTER, $target);

            return true;
        }

        return false;
    }

    private function handleOneShot(Player $player, Player $target, Weapon $blaster): bool
    {
        $oneShotRate = $this->modifierService->getEventModifiedValue(
            $player,
            [ActionTypeEnum::ACTION_SHOOT],
            ModifierTargetEnum::CRITICAL_PERCENTAGE,
            $blaster->getOneShotRate(),
            $this->getActionName(),
            new \DateTime()
        );

        if ($this->randomService->isSuccessful($oneShotRate)) {
            $deathEvent = new PlayerEvent(
                $target,
                EndCauseEnum::BEHEADED,
                new \DateTime()
            );

            $this->eventDispatcher->dispatch($deathEvent, PlayerEvent::DEATH_PLAYER);

            return true;
        }

        return false;
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
