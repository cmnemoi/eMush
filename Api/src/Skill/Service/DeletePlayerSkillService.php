<?php

declare(strict_types=1);

namespace Mush\Skill\Service;

use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Repository\SkillRepositoryInterface;

final class DeletePlayerSkillService
{
    public function __construct(
        private ModifierCreationServiceInterface $modifierCreationService,
        private SkillRepositoryInterface $skillRepository
    ) {}

    public function execute(SkillEnum $skillName, Player $player): void
    {
        $skill = $player->getSkillByNameOrThrow($skillName);

        $this->deleteSkillModifiers($skill);
        $this->skillRepository->delete($skill);
    }

    private function deleteSkillModifiers(Skill $skill): void
    {
        $player = $skill->getPlayer();
        $now = new \DateTime();

        /** @var AbstractModifierConfig $modifierConfig */
        foreach ($player->getAllModifierConfigs() as $modifierConfig) {
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
