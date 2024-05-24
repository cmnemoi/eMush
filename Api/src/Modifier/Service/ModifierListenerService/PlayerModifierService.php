<?php

namespace Mush\Modifier\Service\ModifierListenerService;

use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Player\Event\PlayerChangedPlaceEvent;

final class PlayerModifierService implements PlayerModifierServiceInterface
{
    private ModifierCreationServiceInterface $modifierCreationService;

    public function __construct(
        ModifierCreationServiceInterface $modifierCreationService,
    ) {
        $this->modifierCreationService = $modifierCreationService;
    }

    public function playerEnterRoom(PlayerChangedPlaceEvent $event): void
    {
        $player = $event->getPlayer();
        $place = $player->getPlace();
        $tags = $event->getTags();
        $time = $event->getTime();

        foreach ($player->getStatuses() as $status) {
            $statusConfig = $status->getStatusConfig();

            /** @var AbstractModifierConfig $modifierConfig */
            foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
                if ($modifierConfig->getModifierRange() === ModifierHolderClassEnum::PLACE) {
                    $this->modifierCreationService->createModifier($modifierConfig, $place, $tags, $time);
                }
            }
        }
    }

    public function playerLeaveRoom(PlayerChangedPlaceEvent $event): void
    {
        $player = $event->getPlayer();
        $oldPlace = $event->getOldPlace();
        $tags = $event->getTags();
        $time = $event->getTime();

        foreach ($player->getStatuses() as $status) {
            $statusConfig = $status->getStatusConfig();

            /** @var AbstractModifierConfig $modifierConfig */
            foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
                if ($modifierConfig->getModifierRange() === ModifierHolderClassEnum::PLACE) {
                    $this->modifierCreationService->deleteModifier($modifierConfig, $oldPlace, $tags, $time);
                }
            }
        }
    }
}
