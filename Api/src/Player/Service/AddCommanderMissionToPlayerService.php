<?php

declare(strict_types=1);

namespace Mush\Player\Service;

use Mush\Player\Entity\CommanderMission;
use Mush\Player\Entity\Player;
use Mush\Player\Repository\CommanderMissionRepositoryInterface;

final readonly class AddCommanderMissionToPlayerService
{
    public function __construct(private CommanderMissionRepositoryInterface $commanderMissionRepository) {}

    public function execute(Player $commander, Player $subordinate, string $mission): void
    {
        $this->commanderMissionRepository->save(
            new CommanderMission(
                commander: $commander,
                subordinate: $subordinate,
                mission: $mission,
            )
        );
    }
}
