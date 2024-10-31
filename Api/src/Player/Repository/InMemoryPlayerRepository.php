<?php

declare(strict_types=1);

namespace Mush\Player\Repository;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;

final class InMemoryPlayerRepository implements PlayerRepositoryInterface
{
    private array $players = [];

    public function save(Player $player): void
    {
        $this->players[$player->getName()] = $player;
    }

    public function delete(Player $player): void
    {
        unset($this->players[$player->getName()]);
    }

    public function clear(): void
    {
        $this->players = [];
    }

    public function findOneByNameAndDaedalus(string $name, Daedalus $daedalus): ?Player
    {
        $player = $this->findOneByName($name);
        if ($player === null) {
            return null;
        }
        if ($player->getDaedalus() !== $daedalus) {
            return null;
        }

        return $player;
    }

    public function startTransaction(): void
    {
        // No transaction in memory
    }

    public function commitTransaction(): void
    {
        // No transaction in memory
    }

    public function rollbackTransaction(): void
    {
        // No transaction in memory
    }

    public function lockAndRefresh(Player $player, int $mode): void
    {
        // No locks in memory
    }

    public function getAll(): array
    {
        return array_values($this->players);
    }

    public function findById(int $id): ?Player
    {
        foreach ($this->players as $player) {
            if ($player->getId() === $id) {
                return $player;
            }
        }

        return null;
    }

    public function findOneByName(string $name): ?Player
    {
        foreach ($this->players as $player) {
            if ($player->getName() === $name) {
                return $player;
            }
        }

        return null;
    }
}
