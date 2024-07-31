<?php

declare(strict_types=1);

namespace Mush\Skill\UseCase;

use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Service\StatusServiceInterface;

final class ChooseSkillUseCase
{
    public function __construct(
        private ModifierCreationServiceInterface $modifierCreationService,
        private PlayerRepositoryInterface $playerRepository,
        private StatusServiceInterface $statusService,
    ) {}

    public function execute(ChooseSkillDto $chooseSkillDto): void
    {
        [$skillName, $player] = $chooseSkillDto->toArgs();
        if ($player->hasSkill($skillName)) {
            return;
        }

        $skill = $this->createSkillForPlayer($skillName, $player);
        $this->createSkillModifiers($skill);
        $this->createSkillPoints($skill);
    }

    private function createSkillForPlayer(SkillEnum $skillName, Player $player): Skill
    {
        $skill = new Skill(skillConfig: $player->getSkillConfigByNameOrThrow($skillName), player: $player);
        $this->playerRepository->save($player);

        return $skill;
    }

    private function createSkillModifiers(Skill $skill): void
    {
        foreach ($skill->getModifierConfigs() as $modifierConfig) {
            $modifierHolder = match ($modifierConfig->getModifierRange()) {
                ModifierHolderClassEnum::PLAYER => $skill->getPlayer(),
                ModifierHolderClassEnum::DAEDALUS => $skill->getDaedalus(),
                default => throw new \InvalidArgumentException("You can't create skill modifier {$modifierConfig->getName()} on a {$modifierConfig->getModifierRange()} holder !"),
            };

            $this->modifierCreationService->createModifier($modifierConfig, $modifierHolder);
        }
    }

    private function createSkillPoints(Skill $skill): void
    {
        $this->statusService->createStatusFromConfig(
            statusConfig: $skill->getSkillPointConfig(),
            holder: $skill->getPlayer()
        );
    }
}
