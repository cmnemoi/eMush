<?php

namespace Mush\Modifier\Service;

use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Player\Entity\Player;

class DiseaseModifierService implements DiseaseModifierServiceInterface
{
    private ModifierServiceInterface $modifierService;

    public function __construct(
        ModifierServiceInterface $modifierService,
    ) {
        $this->modifierService = $modifierService;
    }

    public function newDisease(Player $player, DiseaseConfig $diseaseConfig): void
    {
        foreach ($diseaseConfig->getModifierConfigs() as $modifierConfig) {
            $holder = $this->getModifierHolderFromConfig($player, $modifierConfig);
            if ($holder === null) {
                return;
            }

            $this->modifierService->createModifier($modifierConfig, $player);
        }
    }

    public function cureDisease(Player $player, DiseaseConfig $diseaseConfig): void
    {
        foreach ($diseaseConfig->getModifierConfigs() as $modifierConfig) {
            $holder = $this->getModifierHolderFromConfig($player, $modifierConfig);
            if ($holder === null) {
                return;
            }

            $this->modifierService->deleteModifier($modifierConfig, $player);
        }
    }

    private function getModifierHolderFromConfig(Player $player, ModifierConfig $modifierConfig): ?ModifierHolder
    {
        switch ($modifierConfig->getReach()) {
            case ModifierReachEnum::DAEDALUS:
                return $player->getDaedalus();
            case ModifierReachEnum::PLACE:
                return $player->getPlace();
            case ModifierReachEnum::PLAYER:
            case ModifierReachEnum::TARGET_PLAYER:
                return $player;
        }

        return null;
    }
}
