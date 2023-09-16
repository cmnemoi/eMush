<?php

namespace Mush\Modifier\Service\ModifierListenerService;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EquipmentModifierService implements EquipmentModifierServiceInterface
{
    private ModifierCreationServiceInterface $modifierCreationService;

    public function __construct(
        ModifierCreationServiceInterface $modifierCreationService,
    ) {
        $this->modifierCreationService = $modifierCreationService;
    }

    public function gearCreated(GameEquipment $gameEquipment, array $tags, \DateTime $time): void
    {
        $player = $gameEquipment->getHolder();
        if (!$player instanceof Player) {
            $player = null;
        }

        $this->createGearModifiers(
            $gameEquipment,
            ModifierHolderClassEnum::getAllReaches(),
            $tags,
            $time,
            $player
        );
    }

    public function gearDestroyed(GameEquipment $gameEquipment, array $tags, \DateTime $time): void
    {
        $player = $gameEquipment->getHolder();
        if (!$player instanceof Player) {
            $player = null;
        }

        $this->deleteGearModifiers(
            $gameEquipment,
            ModifierHolderClassEnum::getAllReaches(),
            $tags,
            $time,
            $player
        );
    }

    public function takeEquipment(GameEquipment $gameEquipment, Player $player, array $tags, \DateTime $time): void
    {
        if ($gameEquipment->isBroken()) {
            return;
        }

        $this->createGearModifiers(
            $gameEquipment,
            [ModifierHolderClassEnum::PLAYER, ModifierHolderClassEnum::TARGET_PLAYER],
            $tags,
            $time,
            $player
        );
        $this->createEquipmentStatusModifiers(
            $gameEquipment,
            [ModifierHolderClassEnum::PLAYER, ModifierHolderClassEnum::TARGET_PLAYER],
            $tags,
            $time,
            $player
        );
    }

    public function dropEquipment(GameEquipment $gameEquipment, Player $player, array $tags, \DateTime $time): void
    {
        if ($gameEquipment->isBroken()) {
            return;
        }

        $this->deleteGearModifiers(
            $gameEquipment,
            [ModifierHolderClassEnum::PLAYER, ModifierHolderClassEnum::TARGET_PLAYER],
            $tags,
            $time,
            $player
        );
        $this->deleteEquipmentStatusModifiers(
            $gameEquipment,
            [ModifierHolderClassEnum::PLAYER, ModifierHolderClassEnum::TARGET_PLAYER],
            $tags,
            $time,
            $player
        );
    }

    public function equipmentLeaveRoom(GameEquipment $gameEquipment, Place $place, array $tags, \DateTime $time): void
    {
        if ($gameEquipment->isBroken()) {
            return;
        }

        $this->deleteGearModifiers($gameEquipment, [ModifierHolderClassEnum::PLACE], $tags, $time, null);
        $this->deleteEquipmentStatusModifiers($gameEquipment, [ModifierHolderClassEnum::PLACE], $tags, $time, null);
    }

    public function equipmentEnterRoom(GameEquipment $gameEquipment, Place $place, array $tags, \DateTime $time): void
    {
        if ($gameEquipment->isBroken()) {
            return;
        }

        $this->createGearModifiers($gameEquipment, [ModifierHolderClassEnum::PLACE], $tags, $time, null);
        $this->createEquipmentStatusModifiers($gameEquipment, [ModifierHolderClassEnum::PLACE], $tags, $time, null);
    }

    private function getChargeStatus(?string $modifierName, StatusHolderInterface $statusHolder): ?ChargeStatus
    {
        if ($modifierName === null) {
            return null;
        }

        $charges = $statusHolder->getStatuses()->filter(function (Status $status) use ($modifierName) {
            return $status instanceof ChargeStatus
                && $status->hasDischargeStrategy($modifierName);
        });

        if ($charges->count() > 0) {
            return $charges->first();
        } elseif ($charges->count() === 0) {
            return null;
        } else {
            throw new LogicException('there should be maximum 1 chargeStatus with this dischargeStrategy on this statusHolder');
        }
    }

    private function createGearModifiers(
        GameEquipment $gameEquipment,
        array $reaches,
        array $tags,
        \DateTime $time,
        ?Player $player
    ): void {
        $this->createModifiersWithName(
            $this->getGearModifierConfigs($gameEquipment),
            $reaches,
            $gameEquipment,
            $tags,
            $time,
            $player,
        );
    }

    private function deleteGearModifiers(GameEquipment $gameEquipment, array $reaches, array $tags, \DateTime $time, ?Player $player): void
    {
        foreach ($this->getGearModifierConfigs($gameEquipment) as $modifierConfig) {
            if (in_array($modifierConfig->getModifierRange(), $reaches)) {
                $holder = $this->getModifierHolderFromConfig($gameEquipment, $modifierConfig, $player);
                if ($holder === null) {
                    return;
                }

                $this->modifierCreationService->deleteModifier($modifierConfig, $holder, $tags, $time, $player);
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

    private function createEquipmentStatusModifiers(
        GameEquipment $gameEquipment,
        array $reaches,
        array $tags,
        \DateTime $time,
        ?Player $player
    ): void {
        foreach ($gameEquipment->getStatuses() as $status) {
            $statusConfig = $status->getStatusConfig();
            $this->createModifiersWithName(
                $statusConfig->getModifierConfigs(),
                $reaches,
                $gameEquipment,
                $tags,
                $time,
                $player
            );
        }
    }

    private function deleteEquipmentStatusModifiers(
        GameEquipment $gameEquipment,
        array $reaches,
        array $tags,
        \DateTime $time,
        ?Player $player
    ): void {
        foreach ($gameEquipment->getStatuses() as $status) {
            $statusConfig = $status->getStatusConfig();

            foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
                if (in_array($modifierConfig->getModifierRange(), $reaches)) {
                    $holder = $this->getModifierHolderFromConfig($gameEquipment, $modifierConfig, $player);
                    if ($holder === null) {
                        return;
                    }

                    $this->modifierCreationService->deleteModifier($modifierConfig, $holder, $tags, $time, $player);
                }
            }
        }
    }

    private function createModifiersWithName(
        Collection $modifiers,
        array $reaches,
        GameEquipment $gameEquipment,
        array $tags,
        \DateTime $time,
        ?Player $player
    ): void {
        foreach ($modifiers as $modifierConfig) {
            if (in_array($modifierConfig->getModifierRange(), $reaches)) {
                $charge = $this->getChargeStatus($modifierConfig->getModifierName(), $gameEquipment);

                $holder = $this->getModifierHolderFromConfig($gameEquipment, $modifierConfig, $player);
                if ($holder === null) {
                    return;
                }

                $this->modifierCreationService->createModifier(
                    $modifierConfig,
                    $holder,
                    $tags,
                    $time,
                    $player,
                    $charge
                );
            }
        }
    }

    private function getModifierHolderFromConfig(GameEquipment $gameEquipment, AbstractModifierConfig $modifierConfig, ?Player $player): ?ModifierHolder
    {
        switch ($modifierConfig->getModifierRange()) {
            case ModifierHolderClassEnum::DAEDALUS:
                return $gameEquipment->getDaedalus();
            case ModifierHolderClassEnum::PLACE:
                return $gameEquipment->getPlace();
            case ModifierHolderClassEnum::EQUIPMENT:
                return $gameEquipment;
            case ModifierHolderClassEnum::PLAYER:
            case ModifierHolderClassEnum::TARGET_PLAYER:
                $player = $player ?: $gameEquipment->getHolder();
                if ($player instanceof Player) {
                    return $player;
                }
        }

        return null;
    }
}
