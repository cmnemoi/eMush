<?php

declare(strict_types=1);

namespace Mush\Skill\Service;

use Mush\Game\Exception\GameException;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Repository\SkillConfigRepositoryInterface;
use Mush\Status\Service\StatusServiceInterface;

/**
 * /!\ Do not use this service for usual skill selection ! Use ChooseSkillUseCase instead, or verify that the player has the skill before adding it. /!\.
 */
class AddSkillToPlayerService
{
    public function __construct(
        private ModifierCreationServiceInterface $modifierCreationService,
        private PlayerRepositoryInterface $playerRepository,
        private SkillConfigRepositoryInterface $skillConfigRepository,
        private StatusServiceInterface $statusService,
    ) {}

    public function execute(SkillEnum $skill, Player $player): void
    {
        $this->checkPlayerDoesNotHaveSkill($skill, $player);

        $skill = $this->createSkillForPlayer($skill, $player);

        $this->createSkillModifiers($skill);
        $this->createSkillPoints($skill);
    }

    private function checkPlayerDoesNotHaveSkill(SkillEnum $skill, Player $player): void
    {
        if ($player->hasSkill($skill)) {
            throw new GameException('You already have this skill!');
        }
    }

    private function createSkillForPlayer(SkillEnum $skill, Player $player): Skill
    {
        $skillConfig = $this->skillConfigRepository->findOneByNameAndDaedalusOrThrow($skill, $player->getDaedalus());
        $skill = new Skill(skillConfig: $skillConfig, player: $player);
        $this->playerRepository->save($player);

        return $skill;
    }

    private function createSkillModifiers(Skill $skill): void
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

    private function createSkillPoints(Skill $skill): void
    {
        $this->statusService->createStatusFromConfig(
            statusConfig: $skill->getSkillPointConfig(),
            holder: $skill->getPlayer()
        );
    }
}
