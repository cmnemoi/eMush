<?php

namespace Mush\Modifier\Service;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Entity\PlayerModifier;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class GearModifierService implements GearModifierServiceInterface
{
    private ModifierServiceInterface $modifierService;

    public function __construct(
        ModifierServiceInterface $modifierService,
    ) {
        $this->modifierService = $modifierService;
    }

    public function gearCreated(GameEquipment $gameEquipment): void
    {
        if ($gearMechanic = $gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::GEAR)) {
            if (!$gearMechanic instanceof Gear) {
                throw new UnexpectedTypeException($gearMechanic, Gear::class);
            }

            if ($gameEquipment instanceof GameItem) {
                $player = $gameEquipment->getPlayer();
            } else {
                $player = null;
            }

            $place = $gameEquipment->getCurrentPlace();

            foreach ($gearMechanic->getModifierConfigs() as $modifierConfig) {
                $this->createModifier($modifierConfig, $gameEquipment, $place, $player);
            }
        }
    }

    public function gearDestroyed(GameEquipment $gameEquipment): void
    {
        if ($gearMechanic = $gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::GEAR)) {
            if (!$gearMechanic instanceof Gear) {
                throw new UnexpectedTypeException($gearMechanic, Gear::class);
            }

            if ($gameEquipment instanceof GameItem) {
                $player = $gameEquipment->getPlayer();
            } else {
                $player = null;
            }
            $place = $gameEquipment->getCurrentPlace();

            foreach ($gearMechanic->getModifierConfigs() as $modifierConfig) {
                $this->deleteModifier($modifierConfig, $place, $player);
            }
        }
    }

    public function takeGear(GameEquipment $gameEquipment, Player $player): void
    {
        if ($gearMechanic = $gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::GEAR)) {
            if (!$gearMechanic instanceof Gear) {
                throw new UnexpectedTypeException($gearMechanic, Gear::class);
            }

            foreach ($gearMechanic->getModifierConfigs() as $modifierConfig) {
                if ($modifierConfig->getReach() === ModifierReachEnum::PLAYER || $modifierConfig->getReach() === ModifierReachEnum::TARGET_PLAYER) {
                    $modifier = new PlayerModifier();
                    $modifier
                        ->setPlayer($player)
                        ->setModifierConfig($modifierConfig)
                    ;

                    if (($charge = $gameEquipment->getStatusByName(EquipmentStatusEnum::CHARGES))) {
                        if (!$charge instanceof ChargeStatus) {
                            throw new UnexpectedTypeException($charge, ChargeStatus::class);
                        }

                        $modifier->setCharge($charge);
                    }

                    $this->modifierService->persist($modifier);
                }
            }
        }
    }

    public function dropGear(GameEquipment $gameEquipment, Player $player): void
    {
        if ($gearMechanic = $gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::GEAR)) {
            if (!$gearMechanic instanceof Gear) {
                throw new UnexpectedTypeException($gearMechanic, Gear::class);
            }

            foreach ($gearMechanic->getModifierConfigs() as $modifierConfig) {
                if ($modifierConfig->getReach() === ModifierReachEnum::PLAYER || $modifierConfig->getReach() === ModifierReachEnum::TARGET_PLAYER) {
                    $gearModifier = $player->getModifiers()->getModifierFromConfig($modifierConfig);

                    $this->modifierService->delete($gearModifier);
                }
            }
        }
    }

    private function createModifier(ModifierConfig $modifierConfig, GameEquipment $gameEquipment, Place $place, ?Player $player): void
    {
        $charge = $gameEquipment->getStatusByName(EquipmentStatusEnum::CHARGES);
        if (!$charge instanceof ChargeStatus) {
            throw new UnexpectedTypeException($charge, ChargeStatus::class);
        }

        $this->modifierService->createModifier($modifierConfig, $place->getDaedalus(), $place, $player, null, $charge);
    }

    private function deleteModifier(ModifierConfig $modifierConfig, Place $place, ?Player $player): void
    {
        switch ($modifierConfig->getReach()) {
            case ModifierReachEnum::PLAYER:
            case ModifierReachEnum::TARGET_PLAYER:
                if ($player !== null) {
                    $gearModifier = $player->getModifiers()->getModifierFromConfig($modifierConfig);

                    $this->modifierService->delete($gearModifier);
                }

                return;

            case ModifierReachEnum::DAEDALUS:
                $gearModifier = $place->getDaedalus()->getModifiers()->getModifierFromConfig($modifierConfig);

                $this->modifierService->delete($gearModifier);

                return;

            case ModifierReachEnum::PLACE:
                $gearModifier = $place->getModifiers()->getModifierFromConfig($modifierConfig);

                $this->modifierService->delete($gearModifier);

                return;
        }
    }
}
