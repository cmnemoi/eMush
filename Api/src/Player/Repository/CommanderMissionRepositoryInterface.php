<?php

declare(strict_types=1);

namespace Mush\Player\Repository;

use Mush\Player\Entity\CommanderMission;

interface CommanderMissionRepositoryInterface
{
    public function findByIdOrThrow(int $id): CommanderMission;

    public function save(CommanderMission $commanderMission): void;
}
