<?php

namespace Mush\Modifier\Service;

use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Entity\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
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

    private function getModifierHolderFromConfig(Player $player, VariableEventModifierConfig $modifierConfig): ?ModifierHolder
    {
        switch ($modifierConfig->getModifierHolderClass()) {
            case ModifierHolderClassEnum::DAEDALUS:
                return $player->getDaedalus();
            case ModifierHolderClassEnum::PLACE:
                return $player->getPlace();
            case ModifierHolderClassEnum::PLAYER:
            case ModifierHolderClassEnum::TARGET_PLAYER:
                return $player;
        }

        return null;
    }
}
