<?php

namespace Mush\Player\Repository;

use Mush\Player\Entity\ClosedPlayer;

final class InMemoryClosedPlayerRepository implements ClosedPlayerRepositoryInterface
{
    private array $closedPlayers = [];

    public function save(ClosedPlayer $closedPlayer): void
    {
        $this->closedPlayers[$closedPlayer->getId()] = $closedPlayer;
    }

    public function clear(): void
    {
        $this->closedPlayers = [];
    }
}
