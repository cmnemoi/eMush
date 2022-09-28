<?php

namespace Mush\Modifier\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EquipmentModifierService implements EquipmentModifierServiceInterface
{
    private ModifierServiceInterface $modifierService;

    public function __construct(
        ModifierServiceInterface $modifierService,
    ) {
        $this->modifierService = $modifierService;
    }

    public function gearCreated(GameEquipment $gameEquipment): void
    {
        $player = $gameEquipment->getHolder();
        if (!$player instanceof Player) {
            $player = null;
        }

        $this->createGearModifiers(
            $gameEquipment,
            ModifierReachEnum::getAllReaches(),
            $player
        );
    }

    public function gearDestroyed(GameEquipment $gameEquipment): void
    {
        $player = $gameEquipment->getHolder();
        if (!$player instanceof Player) {
            $player = null;
        }

        $this->deleteGearModifiers(
            $gameEquipment,
            ModifierReachEnum::getAllReaches(),
            $player
        );
    }

    public function takeEquipment(GameEquipment $gameEquipment, Player $player): void
    {
        if ($gameEquipment->isBroken()) {
            return;
        }

        $this->createGearModifiers($gameEquipment, [ModifierReachEnum::PLAYER, ModifierReachEnum::TARGET_PLAYER], $player);
        $this->createEquipmentStatusModifiers($gameEquipment, [ModifierReachEnum::PLAYER, ModifierReachEnum::TARGET_PLAYER], $player);
    }

    public function dropEquipment(GameEquipment $gameEquipment, Player $player): void
    {
        if ($gameEquipment->isBroken()) {
            return;
        }

        $this->deleteGearModifiers(
            $gameEquipment,
            [ModifierReachEnum::PLAYER, ModifierReachEnum::TARGET_PLAYER],
            $player
        );
        $this->deleteEquipmentStatusModifiers($gameEquipment, [ModifierReachEnum::PLAYER, ModifierReachEnum::TARGET_PLAYER], $player);
    }

    public function equipmentLeaveRoom(GameEquipment $gameEquipment, Place $place): void
    {
        if ($gameEquipment->isBroken()) {
            return;
        }

        $this->deleteGearModifiers($gameEquipment, [ModifierReachEnum::PLACE], null);
        $this->deleteEquipmentStatusModifiers($gameEquipment, [ModifierReachEnum::PLACE], null);
    }

    public function equipmentEnterRoom(GameEquipment $gameEquipment, Place $place): void
    {
        if ($gameEquipment->isBroken()) {
            return;
        }

        $this->createGearModifiers($gameEquipment, [ModifierReachEnum::PLACE], null);
        $this->createEquipmentStatusModifiers($gameEquipment, [ModifierReachEnum::PLACE], null);
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

    private function createGearModifiers(GameEquipment $gameEquipment, array $reaches, ?Player $player): void
    {
        $this->createModifiersWithName(
            $this->getGearModifierConfigs($gameEquipment),
            $reaches,
            $gameEquipment,
            $player,
        );
    }

    private function deleteGearModifiers(GameEquipment $gameEquipment, array $reaches, ?Player $player): void
    {
        /* @var ModifierConfig $modifierConfig */
        foreach ($this->getGearModifierConfigs($gameEquipment) as $modifierConfig) {
            if (in_array($modifierConfig->getReach(), $reaches)) {
                $holder = $this->getModifierHolderFromConfig($gameEquipment, $modifierConfig, $player);
                if ($holder === null) {
                    return;
                }

                $this->modifierService->deleteModifier($modifierConfig, $holder);
            }
        }
    }

    private function getGearModifierConfigs(GameEquipment $gameEquipment): Collection
    {
        if ($gearMechanic = $gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::GEAR)) {
            if (!$gearMechanic instanceof Gear) {
                throw new UnexpectedTypeException($gearMechanic, Gear::class);
            }

            return $gearMechanic->getModifierConfigs();
        }

        return new ArrayCollection();
    }

    private function createEquipmentStatusModifiers(GameEquipment $gameEquipment, array $reaches, ?Player $player): void
    {
        foreach ($gameEquipment->getStatuses() as $status) {
            $statusConfig = $status->getStatusConfig();
            $this->createModifiersWithName(
                $statusConfig->getModifierConfigs(),
                $reaches,
                $gameEquipment,
                $player
            );
        }
    }

    private function deleteEquipmentStatusModifiers(GameEquipment $gameEquipment, array $reaches, ?Player $player): void
    {
        foreach ($gameEquipment->getStatuses() as $status) {
            $statusConfig = $status->getStatusConfig();

            foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
                if (in_array($modifierConfig->getReach(), $reaches)) {
                    $holder = $this->getModifierHolderFromConfig($gameEquipment, $modifierConfig, $player);
                    if ($holder === null) {
                        return;
                    }

                    $this->modifierService->deleteModifier($modifierConfig, $holder);
                }
            }
        }
    }

    private function createModifiersWithName(
        Collection $modifiers,
        array $reaches,
        GameEquipment $gameEquipment,
        ?Player $player
    ): void {
        foreach ($modifiers as $modifierConfig) {
            if (in_array($modifierConfig->getReach(), $reaches)) {
                $charge = $this->getChargeStatus($modifierConfig->getScope(), $gameEquipment);

                $holder = $this->getModifierHolderFromConfig($gameEquipment, $modifierConfig, $player);
                if ($holder === null) {
                    codecept_debug('c\'est pas bien');
                    return;
                }
                codecept_debug($holder->getClassName());
                $this->modifierService->createModifier(
                    $modifierConfig,
                    $holder,
                    $charge
                );
            }
        }
    }

    private function getModifierHolderFromConfig(GameEquipment $gameEquipment, ModifierConfig $modifierConfig, ?Player $player): ?ModifierHolder
    {
        switch ($modifierConfig->getReach()) {
            case ModifierReachEnum::DAEDALUS:
                return $gameEquipment->getPlace()->getDaedalus();
            case ModifierReachEnum::PLACE:
                return $gameEquipment->getPlace();
            case ModifierReachEnum::EQUIPMENT:
                return $gameEquipment;
            case ModifierReachEnum::PLAYER:
            case ModifierReachEnum::TARGET_PLAYER:
                $player = $player ?: $gameEquipment->getHolder();
                if ($player instanceof Player) {
                    return $player;
                }
        }

        return null;
    }
}
