<?php

namespace Mush\Modifier\Service\ModifierListenerService;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
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
        $gearModifierConfig = $this->getGearModifierConfigs($gameEquipment);
        foreach ($gearModifierConfig as $modifierConfig) {
            $holder = $this->getModifierHolderFromConfig($gameEquipment, $modifierConfig);

            if ($holder === null) {
                return;
            }

            $this->modifierCreationService->createModifier(
                modifierConfig: $modifierConfig,
                holder: $holder,
                modifierProvider: $gameEquipment,
                tags: $tags,
                time: $time,
            );
        }
    }

    public function gearDestroyed(GameEquipment $gameEquipment, array $tags, \DateTime $time): void
    {
        $gearModifierConfig = $this->getGearModifierConfigs($gameEquipment);
        foreach ($gearModifierConfig as $modifierConfig) {
            $holder = $this->getModifierHolderFromConfig($gameEquipment, $modifierConfig);

            if ($holder === null) {
                return;
            }
            $this->modifierCreationService->deleteModifier($modifierConfig, $holder, $tags, $time);
        }
    }

    public function takeEquipment(GameEquipment $gameEquipment, Player $player, array $tags, \DateTime $time): void
    {
        foreach ($gameEquipment->getAllModifierConfigs() as $modifierConfig) {
            if (
                $modifierConfig->getModifierRange() === ModifierHolderClassEnum::PLAYER
                || $modifierConfig->getModifierRange() === ModifierHolderClassEnum::TARGET_PLAYER
            ) {
                $this->modifierCreationService->createModifier(
                    modifierConfig: $modifierConfig,
                    holder: $player,
                    modifierProvider: $gameEquipment,
                    tags: $tags,
                    time: $time,
                );
            }
        }
    }

    public function dropEquipment(GameEquipment $gameEquipment, Player $player, array $tags, \DateTime $time): void
    {
        foreach ($gameEquipment->getAllModifierConfigs() as $modifierConfig) {
            if (
                $modifierConfig->getModifierRange() === ModifierHolderClassEnum::PLAYER
                || $modifierConfig->getModifierRange() === ModifierHolderClassEnum::TARGET_PLAYER
            ) {
                $this->modifierCreationService->deleteModifier($modifierConfig, $player, $tags, $time);
            }
        }
    }

    public function equipmentLeaveRoom(GameEquipment $gameEquipment, Place $place, array $tags, \DateTime $time): void
    {
        $place = $gameEquipment->getPlace();

        foreach ($gameEquipment->getAllModifierConfigs() as $modifierConfig) {
            if (
                $modifierConfig->getModifierRange() === ModifierHolderClassEnum::PLACE
            ) {
                $this->modifierCreationService->deleteModifier($modifierConfig, $place, $tags, $time);
            }
        }
    }

    public function equipmentEnterRoom(GameEquipment $gameEquipment, Place $place, array $tags, \DateTime $time): void
    {
        $place = $gameEquipment->getPlace();

        foreach ($gameEquipment->getAllModifierConfigs() as $modifierConfig) {
            if (
                $modifierConfig->getModifierRange() === ModifierHolderClassEnum::PLACE
            ) {
                $this->modifierCreationService->createModifier(
                    modifierConfig: $modifierConfig,
                    holder: $place,
                    modifierProvider: $gameEquipment,
                    tags: $tags,
                    time: $time,
                );
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

    private function getModifierHolderFromConfig(
        GameEquipment $gameEquipment,
        AbstractModifierConfig $modifierConfig,
    ): ?ModifierHolderInterface {
        switch ($modifierConfig->getModifierRange()) {
            case ModifierHolderClassEnum::DAEDALUS:
                return $gameEquipment->getDaedalus();

            case ModifierHolderClassEnum::PLACE:
                return $gameEquipment->getPlace();

            case ModifierHolderClassEnum::EQUIPMENT:
                return $gameEquipment;

            case ModifierHolderClassEnum::PLAYER:
            case ModifierHolderClassEnum::TARGET_PLAYER:
                $player = $gameEquipment->getHolder();
                if ($player instanceof Player) {
                    return $player;
                }
        }

        return null;
    }
}
