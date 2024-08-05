<?php

declare(strict_types=1);

namespace Mush\Player\Repository;

use Mush\Player\Entity\Player;

final class InMemoryPlayerRepository implements PlayerRepositoryInterface
{
    private array $players = [];

    public function findOneByIdOrThrow(int $playerId): Player
    {
        return $this->players[$playerId] ?? throw new \RuntimeException('Player not found');
    }

    public function save(Player $player): void
    {
        $this->players[$player->getName()] = $player;
    }

    public function clear(): void
    {
        $this->players = [];
    }

    public function findOneByName(string $name): ?Player
    {
        return $this->players[$name] ?? null;
    }
}
