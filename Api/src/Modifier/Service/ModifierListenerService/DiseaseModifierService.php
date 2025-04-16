<?php

namespace Mush\Modifier\Service\ModifierListenerService;

use Mush\Disease\Entity\PlayerDisease;
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

    public function newDisease(Player $player, PlayerDisease $playerDisease, array $tags, \DateTime $time): void
    {
        $diseaseConfig = $playerDisease->getDiseaseConfig();
        foreach ($diseaseConfig->getModifierConfigs() as $modifierConfig) {
            $this->modifierCreationService->createModifier(
                modifierConfig: $modifierConfig,
                holder: $this->getModifierHolderFromConfig($player, $modifierConfig),
                modifierProvider: $playerDisease,
                tags: $tags,
                time: $time,
            );
        }
    }

    public function cureDisease(Player $player, PlayerDisease $playerDisease, array $tags, \DateTime $time): void
    {
        $diseaseConfig = $playerDisease->getDiseaseConfig();
        foreach ($diseaseConfig->getModifierConfigs() as $modifierConfig) {
            $this->modifierCreationService->deleteModifier(
                modifierConfig: $modifierConfig,
                holder: $this->getModifierHolderFromConfig($player, $modifierConfig),
                modifierProvider: $playerDisease->getCreatedAt() < new \DateTime('2025-04-13 20:30:00') ? $player : $playerDisease, // TODO: Remove feature flag after all Daedalus created before this date are finished
                tags: $tags,
                time: $time,
                revertOnRemove: $playerDisease->isActive()
            );
        }
    }

    private function getModifierHolderFromConfig(Player $player, AbstractModifierConfig $modifierConfig): ModifierHolderInterface
    {
        return match ($modifierConfig->getModifierRange()) {
            ModifierHolderClassEnum::DAEDALUS => $player->getDaedalus(),
            ModifierHolderClassEnum::PLACE => $player->getPlace(),
            ModifierHolderClassEnum::PLAYER, ModifierHolderClassEnum::TARGET_PLAYER => $player,
            default => throw new \RuntimeException('Invalid modifier range: ' . $modifierConfig->getModifierRange()),
        };
    }
}
