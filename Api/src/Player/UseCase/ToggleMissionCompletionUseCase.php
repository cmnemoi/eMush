<?php

declare(strict_types=1);

namespace Mush\Player\UseCase;

use Mush\Player\Entity\CommanderMission;
use Mush\Player\Repository\CommanderMissionRepositoryInterface;

final readonly class ToggleMissionCompletionUseCase
{
    public function __construct(private CommanderMissionRepositoryInterface $commanderMissionRepository) {}

    public function execute(CommanderMission $mission): void
    {
        $mission->toggleCompletion();
        $this->commanderMissionRepository->save($mission);
    }
}
