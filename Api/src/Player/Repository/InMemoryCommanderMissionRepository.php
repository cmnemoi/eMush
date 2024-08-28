<?php

declare(strict_types=1);

namespace Mush\Player\Repository;

use Mush\Player\Entity\CommanderMission;

final class InMemoryCommanderMissionRepository implements CommanderMissionRepositoryInterface
{
    private array $commanderMissions = [];

    public function findByIdOrThrow(int $id): CommanderMission
    {
        return array_filter($this->commanderMissions, static fn (CommanderMission $commanderMission) => $commanderMission->getId() === $id)[0] ?? throw new \RuntimeException("CommanderMission {$id} not found");
    }

    public function save(CommanderMission $commanderMission): void
    {
        $commanderMission->setCreatedAt(new \DateTime());
        (new \ReflectionProperty(CommanderMission::class, 'id'))->setValue($commanderMission, \count($this->commanderMissions) + 1);
        $this->commanderMissions[] = $commanderMission;
    }

    public function clear(): void
    {
        $this->commanderMissions = [];
    }

    public function findByCommanderSubordinateAndMission(int $commanderId, int $subordinateId, string $mission): array
    {
        return array_filter($this->commanderMissions, static function (CommanderMission $commanderMission) use ($commanderId, $subordinateId, $mission) {
            return $commanderMission->getCommander()->getId() === $commanderId
                && $commanderMission->getSubordinate()->getId() === $subordinateId
                && $commanderMission->getMission() === $mission;
        });
    }
}
