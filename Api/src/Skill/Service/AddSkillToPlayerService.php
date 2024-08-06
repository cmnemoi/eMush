<?php

declare(strict_types=1);

namespace Mush\Skill\Service;

use Mush\Player\Entity\Player;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Repository\SkillConfigRepositoryInterface;

/**
 * /!\ Do not use this service for usual skill selection ! Use ChooseSkillUseCase instead, or verify that the player has the skill before adding it. /!\.
 */
final class AddSkillToPlayerService
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
        private SkillConfigRepositoryInterface $skillConfigRepository,
    ) {}

    public function execute(SkillEnum $skill, Player $player): void
    {
        $skillConfig = $this->skillConfigRepository->findOneByNameAndDaedalusOrThrow($skill, $player->getDaedalus());
        new Skill(skillConfig: $skillConfig, player: $player);
        $this->playerRepository->save($player);
    }
}
