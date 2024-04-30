<?php

namespace Mush\Modifier\Service\ModifierListenerService;

use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Player\Entity\Player;

class DiseaseModifierService implements DiseaseModifierServiceInterface
{
    private ModifierCreationServiceInterface $modifierCreationService;

    public function __construct(
        ModifierCreationServiceInterface $modifierCreationService,
    ) {
        $this->modifierCreationService = $modifierCreationService;
    }

    public function newDisease(Player $player, DiseaseConfig $diseaseConfig, array $tags, \DateTime $time): void
    {
        foreach ($diseaseConfig->getModifierConfigs() as $modifierConfig) {
            $holder = $this->getModifierHolderFromConfig($player, $modifierConfig);
            if ($holder === null) {
                return;
            }

            $this->modifierCreationService->createModifier($modifierConfig, $holder, $tags, $time);
        }
    }

    public function cureDisease(Player $player, DiseaseConfig $diseaseConfig, array $tags, \DateTime $time): void
    {
        foreach ($diseaseConfig->getModifierConfigs() as $modifierConfig) {
            $holder = $this->getModifierHolderFromConfig($player, $modifierConfig);
            if ($holder === null) {
                return;
            }

            $this->modifierCreationService->deleteModifier($modifierConfig, $holder, $tags, $time);
        }
    }

    private function getModifierHolderFromConfig(Player $player, AbstractModifierConfig $modifierConfig): ?ModifierHolderInterface
    {
        switch ($modifierConfig->getModifierRange()) {
            case ModifierHolderClassEnum::DAEDALUS:
                return $player->getDaedalus();

            case ModifierHolderClassEnum::PLACE:
                return $player->getPlace();

            case ModifierHolderClassEnum::PLAYER:
                return $player;
        }

        return null;
    }
}
