<?php

namespace Mush\Modifier\Service;

use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Player\Entity\Player;

class PlayerModifierService implements PlayerModifierServiceInterface
{
    private ModifierServiceInterface $modifierService;

    public function __construct(
        ModifierService $modifierService
    ) {
        $this->modifierService = $modifierService;
    }

    public function playerEnterRoom(Player $player): void
    {
        $place = $player->getPlace();

        foreach ($player->getStatuses() as $status) {
            $statusConfig = $status->getStatusConfig();
            /** @var ModifierConfig $modifierConfig */
            foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
                if ($modifierConfig->getModifierHolderClass() === ModifierHolderClassEnum::PLACE) {
                    $this->modifierService->createModifier($modifierConfig, $place);
                }
            }
        }
    }

    public function playerLeaveRoom(Player $player): void
    {
        $place = $player->getPlace();

        foreach ($player->getStatuses() as $status) {
            $statusConfig = $status->getStatusConfig();
            /** @var ModifierConfig $modifierConfig */
            foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
                if ($modifierConfig->getModifierHolderClass() === ModifierHolderClassEnum::PLACE) {
                    $this->modifierService->deleteModifier($modifierConfig, $place);
                }
            }
        }
    }
}
