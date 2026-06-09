<?php

declare(strict_types=1);

namespace Mush\Player\Entity;

use Mush\Player\Entity\Collection\PlayerCollection;

/**
 * Interface for entities manipulating a collection of `Player`.
 */
interface PlayerHolderInterface
{
    public function getPlayers(): PlayerCollection;

    public function setPlayers(PlayerCollection $players): static;

    public function setPlayerHolder(Player $player): static;

    public function addPlayer(Player $player): static;

    public function removePlayer(Player $player): static;

    public function getAlivePlayers(): PlayerCollection;

    public function getAlivePlayersExcept(Player $player): PlayerCollection;

    public function getNumberOfPlayersAlive(): int;

    public function getPlayerByName(string $name): ?Player;

    public function getPlayerByNameOrThrow(string $name): Player;

    public function getAlivePlayerByNameOrThrow(string $name): Player;

    public function getHumanPlayers(): PlayerCollection;

    public function getMushPlayers(): PlayerCollection;

    public function getLostPlayers(): PlayerCollection;

    public function getAlivePlayersWithMeansOfCommunication(): PlayerCollection;

    public function getAlivePlayersInSpaceBattle(): PlayerCollection;

    public function getCurrentPariah(): Player;

    public function hasAPariah(): bool;
}
