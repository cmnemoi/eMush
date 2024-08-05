<?php

declare(strict_types=1);

namespace Mush\Skill\UseCase;

use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\Skill\Enum\SkillEnum;

final class DeletePlayerSkillUseCase
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
    ) {}

    public function execute(int $playerId, string $skill): void
    {
        $player = $this->playerRepository->findOneByIdOrThrow($playerId);
        $player->removeSkill($player->getSkillByNameOrThrow(SkillEnum::from($skill)));
        $this->playerRepository->save($player);
    }
}
