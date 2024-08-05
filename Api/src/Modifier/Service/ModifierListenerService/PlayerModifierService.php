<?php

namespace Mush\Modifier\Service\ModifierListenerService;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Player\Event\PlayerChangedPlaceEvent;

final class PlayerModifierService implements PlayerModifierServiceInterface
{
    private ModifierCreationServiceInterface $modifierCreationService;
    private EquipmentModifierServiceInterface $equipmentModifierService;

    public function __construct(
        ModifierCreationServiceInterface $modifierCreationService,
        EquipmentModifierServiceInterface $equipmentModifierService
    ) {
        $this->modifierCreationService = $modifierCreationService;
        $this->equipmentModifierService = $equipmentModifierService;
    }

    public function playerEnterRoom(PlayerChangedPlaceEvent $event): void
    {
        $player = $event->getPlayer();
        $place = $player->getPlace();
        $tags = $event->getTags();
        $time = $event->getTime();

        foreach ($player->getAllModifierConfigs() as $modifierConfig) {
            if ($modifierConfig->getModifierRange() === ModifierHolderClassEnum::PLACE) {
                $this->modifierCreationService->createModifier(
                    modifierConfig: $modifierConfig,
                    holder: $place,
                    modifierProvider: $player,
                    tags: $tags,
                    time: $time
                );
            }
        }

        /** @var GameEquipment $item */
        foreach ($player->getEquipments() as $item) {
            $this->equipmentModifierService->equipmentEnterRoom($item, $place, $tags, $time);
        }
    }

    public function playerLeaveRoom(PlayerChangedPlaceEvent $event): void
    {
        $player = $event->getPlayer();
        $oldPlace = $event->getOldPlace();
        $tags = $event->getTags();
        $time = $event->getTime();

        foreach ($player->getAllModifierConfigs() as $modifierConfig) {
            if ($modifierConfig->getModifierRange() === ModifierHolderClassEnum::PLACE) {
                $this->modifierCreationService->deleteModifier($modifierConfig, $oldPlace, $tags, $time);
            }
        }

        /** @var GameEquipment $item */
        foreach ($player->getEquipments() as $item) {
            $this->equipmentModifierService->equipmentLeaveRoom($item, $oldPlace, $tags, $time);
        }
    }
}
