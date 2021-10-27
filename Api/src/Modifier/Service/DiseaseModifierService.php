<?php

namespace Mush\Modifier\Service;

use Mush\Disease\Entity\DiseaseConfig;
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
        $place = $player->getPlace();
        foreach ($diseaseConfig->getModifierConfigs() as $modifierConfig) {
            $this->modifierService->createModifier($modifierConfig, $player->getDaedalus(), $place, $player, null);
        }
    }

    public function cureDisease(Player $player, DiseaseConfig $diseaseConfig): void
    {
        foreach ($diseaseConfig->getModifierConfigs() as $modifierConfig) {
            $this->modifierService->deleteModifier($modifierConfig, $player->getDaedalus(), $player->getPlace(), $player, null);
        }
    }
}
