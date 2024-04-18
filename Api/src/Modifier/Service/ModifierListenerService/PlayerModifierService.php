<?php

namespace Mush\Modifier\Service\ModifierListenerService;

use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Player\Entity\Player;

class PlayerModifierService implements PlayerModifierServiceInterface
{
    private ModifierCreationServiceInterface $modifierCreationService;

    public function __construct(
        ModifierCreationServiceInterface $modifierCreationService,
    ) {
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
                    $this->modifierCreationService->createModifier($modifierConfig, $place, $tags, $time);
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
                    $this->modifierCreationService->deleteModifier($modifierConfig, $place, $tags, $time);
                }
            }
        }
    }
}
