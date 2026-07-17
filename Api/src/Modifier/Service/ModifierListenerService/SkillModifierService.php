<?php

declare(strict_types=1);

namespace Mush\Modifier\Service\ModifierListenerService;

use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Skill\Entity\Skill;

class SkillModifierService
{
    public function __construct(private ModifierCreationServiceInterface $modifierCreationService) {}

    public function createSkillModifiers(Skill $skill): void
    {
        foreach ($skill->getModifierConfigs() as $modifierConfig) {
            $modifierHolder = match ($modifierConfig->getModifierRange()) {
                ModifierHolderClassEnum::PLAYER, ModifierHolderClassEnum::TARGET_PLAYER => $skill->getPlayer(),
                ModifierHolderClassEnum::PLACE => $skill->getPlayer()->getPlace(),
                ModifierHolderClassEnum::DAEDALUS => $skill->getDaedalus(),
                default => throw new \InvalidArgumentException("You can't create skill modifier {$modifierConfig->getName()} on a {$modifierConfig->getModifierRange()} holder !"),
            };

            $this->modifierCreationService->createModifier(
                modifierConfig: $modifierConfig,
                holder: $modifierHolder,
                modifierProvider: $skill->getPlayer()
            );
        }
    }

    public function deleteSkillModifiers(Skill $skill): void
    {
        $player = $skill->getPlayer();
        $now = new \DateTime();

        foreach ($skill->getAllModifierConfigs() as $modifierConfig) {
            $modifierHolder = match ($modifierConfig->getModifierRange()) {
                ModifierHolderClassEnum::PLAYER, ModifierHolderClassEnum::TARGET_PLAYER => $player,
                ModifierHolderClassEnum::DAEDALUS => $player->getDaedalus(),
                ModifierHolderClassEnum::PLACE => $player->getPlace(),
                default => throw new \LogicException("Modifier holded by {$modifierConfig->getModifierRange()} is not related to skill : cannot delete it"),
            };

            $this->modifierCreationService->deleteModifier(
                modifierConfig: $modifierConfig,
                holder: $modifierHolder,
                modifierProvider: $player,
                tags: [],
                time: $now
            );
        }
    }
}
