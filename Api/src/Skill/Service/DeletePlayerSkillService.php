<?php

declare(strict_types=1);

namespace Mush\Skill\Service;

use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Repository\SkillRepositoryInterface;

final class DeletePlayerSkillService
{
    public function __construct(private SkillRepositoryInterface $skillRepository) {}

    public function execute(SkillEnum $skill, Player $player): void
    {
        $this->skillRepository->delete($player->getSkillByNameOrThrow($skill));
    }
}
