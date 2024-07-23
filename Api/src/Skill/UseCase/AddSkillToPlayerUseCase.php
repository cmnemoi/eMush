<?php

declare(strict_types=1);

namespace Mush\Skill\UseCase;

use Mush\Player\Entity\Player;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillName;

final class AddSkillToPlayerUseCase
{
    public function __construct(private PlayerRepositoryInterface $playerRepository) {}

    public function execute(SkillName $skillName, Player $player): void
    {
        $skillConfig = $player->getSkillConfigByNameOrThrow($skillName);
        $skill = new Skill($skillConfig);

        $player->addSkill($skill);

        $this->playerRepository->save($player);
    }
}
