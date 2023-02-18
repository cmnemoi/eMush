<?php

namespace Mush\Modifier\Service\ModifierListenerService;

use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\EventModifierServiceInterface;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Player\Entity\Player;

class PlayerModifierService implements PlayerModifierServiceInterface
{
    private EventModifierServiceInterface $modifierService;
    private ModifierCreationServiceInterface $modifierCreationService;

    public function __construct(
        EventModifierServiceInterface $modifierService,
        ModifierCreationServiceInterface $modifierCreationService,
    ) {
        $this->modifierService = $modifierService;
        $this->modifierCreationService = $modifierCreationService;
    }

    public function playerEnterRoom(Player $player, array $tags, \DateTime $time): void
    {
        $place = $player->getPlace();

        foreach ($player->getStatuses() as $status) {
            $statusConfig = $status->getStatusConfig();
            /** @var AbstractModifierConfig $modifierConfig */
            foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
                if ($modifierConfig->getModifierRange() === ModifierHolderClassEnum::PLACE) {
                    $this->modifierCreationService->createModifier($modifierConfig, $place, $tags, $time, $player);
                }
            }
        }
    }

    public function playerLeaveRoom(Player $player, array $tags, \DateTime $time): void
    {
        $place = $player->getPlace();

        foreach ($player->getStatuses() as $status) {
            $statusConfig = $status->getStatusConfig();
            /** @var AbstractModifierConfig $modifierConfig */
            foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
                if ($modifierConfig->getModifierRange() === ModifierHolderClassEnum::PLACE) {
                    $this->modifierCreationService->deleteModifier($modifierConfig, $place, $tags, $time, $player);
                }
            }
        }
    }
}
