<?php

namespace Mush\Modifier\Service;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Symfony\Component\Validator\Exception\LogicException;
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
                $this->modifierService->deleteModifier($modifierConfig, $place->getDaedalus(), $place, $player, null);
            }
        }
    }

    public function takeGear(GameEquipment $gameEquipment, Player $player): void
    {
        if ($gameEquipment->isBroken()) {
            return;
        }

        if ($gearMechanic = $gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::GEAR)) {
            if (!$gearMechanic instanceof Gear) {
                throw new UnexpectedTypeException($gearMechanic, Gear::class);
            }

            /** @var ModifierConfig $modifierConfig */
            foreach ($gearMechanic->getModifierConfigs() as $modifierConfig) {
                if (in_array($modifierConfig->getReach(), [ModifierReachEnum::PLAYER, ModifierReachEnum::TARGET_PLAYER])) {
                    $modifier = new Modifier($player, $modifierConfig);

                    $charge = $this->getChargeStatus($modifierConfig->getScope(), $gameEquipment);

                    if ($charge) {
                        $modifier->setCharge($charge);
                    }

                    $this->modifierService->persist($modifier);
                }
            }
        }
    }

    public function dropGear(GameEquipment $gameEquipment, Player $player): void
    {
        if ($gameEquipment->isBroken()) {
            return;
        }

        if ($gearMechanic = $gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::GEAR)) {
            if (!$gearMechanic instanceof Gear) {
                throw new UnexpectedTypeException($gearMechanic, Gear::class);
            }

            foreach ($gearMechanic->getModifierConfigs() as $modifierConfig) {
                if (in_array($modifierConfig->getReach(), [ModifierReachEnum::PLAYER, ModifierReachEnum::TARGET_PLAYER])) {
                    $gearModifier = $player->getModifiers()->getModifierFromConfig($modifierConfig);

                    $this->modifierService->delete($gearModifier);
                }
            }
        }
    }

    public function handleDisplacement(Player $player): void
    {
        foreach ($player->getItems() as $gameItem) {
            if ($gearMechanic = $gameItem->getEquipment()->getMechanicByName(EquipmentMechanicEnum::GEAR)) {
                if (!$gearMechanic instanceof Gear) {
                    throw new UnexpectedTypeException($gearMechanic, Gear::class);
                }

                foreach ($gearMechanic->getModifierConfigs() as $modifierConfig) {
                    if ($modifierConfig->getReach() === ModifierReachEnum::PLACE) {
                        // @TODO once we can set a room in ActionResult
                    }
                }
            }
        }
    }

    private function createModifier(ModifierConfig $modifierConfig, GameEquipment $gameEquipment, Place $place, ?Player $player): void
    {
        $charge = $this->getChargeStatus($modifierConfig->getScope(), $gameEquipment);

        $this->modifierService->createModifier($modifierConfig, $place->getDaedalus(), $place, $player, null, $charge);
    }

    private function getChargeStatus(string $eventName, StatusHolderInterface $statusHolder): ?ChargeStatus
    {
        $charges = $statusHolder->getStatuses()->filter(function (Status $status) use ($eventName) {
            return $status instanceof ChargeStatus &&
                $status->getDischargeStrategy() === $eventName;
        });

        if ($charges->count() > 0) {
            return $charges->first();
        } elseif ($charges->count() === 0) {
            return null;
        } else {
            throw new LogicException('there should be maximum 1 chargeStatus with this dischargeStrategy on this statusHolder');
        }
    }
}
