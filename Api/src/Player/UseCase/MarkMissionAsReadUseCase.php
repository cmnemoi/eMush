<?php

declare(strict_types=1);

namespace Mush\Player\UseCase;

use Mush\Player\Entity\CommanderMission;
use Mush\Player\Repository\CommanderMissionRepositoryInterface;

final readonly class MarkMissionAsReadUseCase
{
    public function __construct(private CommanderMissionRepositoryInterface $commanderMissionRepository) {}

    public function execute(CommanderMission $mission): void
    {
        $mission->markAsRead();
        $this->commanderMissionRepository->save($mission);
    }
}
