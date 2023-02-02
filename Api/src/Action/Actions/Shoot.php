<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\CriticalFail;
use Mush\Action\ActionResult\CriticalSuccess;
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
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Event\QuantityEventInterface;
use Mush\Game\Service\EventServiceInterface;
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
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the shoot action.
 */
class Shoot extends AttemptAction
{
    protected string $name = ActionEnum::SHOOT;

    private ModifierServiceInterface $modifierService;
    private DiseaseCauseServiceInterface $diseaseCauseService;
    protected RandomServiceInterface $randomService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        RandomServiceInterface $randomService,
        ModifierServiceInterface $modifierService,
        DiseaseCauseServiceInterface $diseaseCauseService
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator,
            $logger,
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
    }

    protected function checkResult(): ActionResult
    {
        $player = $this->player;

        $blasterItem = $this->getPlayerBlaster();
        if ($blasterItem == null) {
            $errorMessage = "Shoot::checkResult() - player should have a blaster";
            $this->logger->error($errorMessage, 
                [   
                    'daedalus' => $player->getDaedalus()->getId(),
                    'player' => $player->getId(),
                    'playerInventory' => $player->getEquipments()->toArray()
                ]);
            throw new \Exception($errorMessage);
        }

        /** @var Weapon $blasterWeapon */
        $blasterWeapon = $blasterItem->getMechanics()->first();
        if (!$blasterWeapon instanceof Weapon) {
            $errorMessage = "Shoot::checkResult() - blaster should have a weapon mechanic";
            $this->logger->error($errorMessage, 
                [   
                    'daedalus' => $player->getDaedalus()->getId(),
                    'player' => $player->getId(),
                    'blaster' => $blasterWeapon->getId(),
                ]);
            throw new \Exception($errorMessage);
        }

        $result = parent::checkResult();

        if ($result instanceof Success) {
            if ($this->isOneShot($player, $blasterWeapon)) {
                return new OneShot();
            }

            if ($this->isCriticalSuccess($player, $blasterWeapon)) {
                return new CriticalSuccess();
            }

            return new Success();
        } else {
            if ($this->isCriticalFail($player, $blasterWeapon)) {
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

        $blasterItem = $this->getPlayerBlaster();
        if ($blasterItem == null) {
            $errorMessage = "Shoot::applyEffect() - player should have a blaster";
            $this->logger->error($errorMessage, 
                [   
                    'daedalus' => $player->getDaedalus()->getId(),
                    'player' => $player->getId(),
                    'playerInventory' => $player->getEquipments()->toArray()
                ]);
            throw new \Exception($errorMessage);
        }

        /** @var Weapon $blasterWeapon */
        $blasterWeapon = $blasterItem->getMechanics()->first();
        if (!$blasterWeapon instanceof Weapon) {
            $errorMessage = "Shoot::applyEffect() - blaster should have a weapon mechanic";
            $this->logger->error($errorMessage, 
                [   
                    'daedalus' => $player->getDaedalus()->getId(),
                    'player' => $player->getId(),
                    'blaster' => $blasterWeapon->getId(),
                ]);
            throw new \Exception($errorMessage);
        }

        if ($result instanceof Success) {
            if ($result instanceof OneShot) {
                $reasons = $this->getAction()->getActionTags();
                $reasons[] = EndCauseEnum::BEHEADED;
                $deathEvent = new PlayerEvent(
                    $target,
                    $reasons,
                    new \DateTime()
                );

                $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);

                return;
            }

            $damage = intval($this->randomService->getSingleRandomElementFromProbaArray($blasterWeapon->getBaseDamageRange()));

            if ($result instanceof CriticalSuccess) {
                $this->diseaseCauseService->handleDiseaseForCause(DiseaseCauseEnum::CRITICAL_SUCCESS_BLASTER, $target);
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
                $this->diseaseCauseService->handleDiseaseForCause(DiseaseCauseEnum::CRITICAL_FAIL_BLASTER, $player);
            }
        }
    }

    private function getPlayerBlaster(): ?EquipmentConfig
    {
        return $this->player->getEquipments()->filter(
            fn (GameItem $gameItem) => $gameItem->getName() === ItemEnum::BLASTER && $gameItem->isOperational()
        )->first()->getEquipment();
    }

    private function isCriticalFail(Player $player, Weapon $blaster): bool
    {
        $criticalFailRate = $this->modifierService->getEventModifiedValue(
            $player,
            [ActionTypeEnum::ACTION_SHOOT],
            ModifierTargetEnum::CRITICAL_PERCENTAGE,
            $blaster->getCriticalFailRate(),
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );

        return $this->randomService->isSuccessful($criticalFailRate);
    }

    private function isCriticalSuccess(Player $player, Weapon $blaster): bool
    {
        $criticalSuccessRate = $this->modifierService->getEventModifiedValue(
            $player,
            [ActionTypeEnum::ACTION_SHOOT],
            ModifierTargetEnum::CRITICAL_PERCENTAGE,
            $blaster->getCriticalSuccessRate(),
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );

        return $this->randomService->isSuccessful($criticalSuccessRate);
    }

    private function isOneShot(Player $player, Weapon $blaster): bool
    {
        $oneShotRate = $this->modifierService->getEventModifiedValue(
            $player,
            [ActionTypeEnum::ACTION_SHOOT],
            ModifierTargetEnum::CRITICAL_PERCENTAGE,
            $blaster->getOneShotRate(),
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
            QuantityEventInterface::CHANGE_VARIABLE
        );
    }
}
