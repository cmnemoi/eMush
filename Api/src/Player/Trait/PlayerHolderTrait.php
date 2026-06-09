<?php

declare(strict_types=1);

namespace Mush\Player\Trait;

use Doctrine\Common\Collections\Collection;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerHolderInterface;
use Mush\Status\Enum\PlayerStatusEnum;

/**
 * Trait for entities manipulating a collection of `Player`.
 *
 * The using entity should still implement the `getPlayer()` and
 * `setPlayerHolder()` methods.
 *
 * @mixin PlayerHolderInterface
 *
 * @property Collection $players
 */
trait PlayerHolderTrait
{
    public function setPlayers(PlayerCollection $players): static
    {
        $this->players = $players;

        foreach ($players as $player) {
            $this->setPlayerHolder($player);
        }

        return $this;
    }

    public function addPlayer(Player $player): static
    {
        if (!$this->players->contains($player)) {
            $this->players->add($player);
            $this->setPlayerHolder($player);
        }

        return $this;
    }

    public function removePlayer(Player $player): static
    {
        $this->players->removeElement($player);

        return $this;
    }

    public function getAlivePlayers(): PlayerCollection
    {
        return $this->getPlayers()->getPlayerAlive();
    }

    public function getAlivePlayersExcept(Player $player): PlayerCollection
    {
        return $this->getAlivePlayers()->getAllExcept($player);
    }

    public function getNumberOfPlayersAlive(): int
    {
        return $this->getPlayers()->getPlayerAlive()->count();
    }

    public function getPlayerByName(string $name): ?Player
    {
        return $this->getPlayers()->getPlayerByName($name);
    }

    public function getPlayerByNameOrThrow(string $name): Player
    {
        $player = $this->getPlayers()->getPlayerByName($name);
        if (!$player) {
            throw new \RuntimeException(static::class . " should have a player named {$name}");
        }

        return $player;
    }

    public function getAlivePlayerByNameOrThrow(string $name): Player
    {
        $player = $this->getAlivePlayers()->getPlayerByName($name);
        if (!$player) {
            throw new \RuntimeException(static::class . " should have an alive player named {$name}");
        }

        return $player;
    }

    public function getHumanPlayers(): PlayerCollection
    {
        return $this->getPlayers()->getHumanPlayer();
    }

    public function getMushPlayers(): PlayerCollection
    {
        return $this->getPlayers()->getMushPlayer();
    }

    public function getLostPlayers(): PlayerCollection
    {
        return $this->getPlayers()->getPlayerAlive()->filter(static fn (Player $player) => $player->hasStatus(PlayerStatusEnum::LOST));
    }

    public function getAlivePlayersWithMeansOfCommunication(): PlayerCollection
    {
        return $this->getAlivePlayers()->filter(static fn (Player $player) => $player->hasMeansOfCommunication());
    }

    public function getAlivePlayersInSpaceBattle(): PlayerCollection
    {
        return $this->getAlivePlayers()->filter(static fn (Player $player) => $player->isInSpaceBattle());
    }

    public function getCurrentPariah(): Player
    {
        return $this->getAlivePlayers()->getPlayerWithStatusOrThrow(PlayerStatusEnum::PARIAH);
    }

    public function hasAPariah(): bool
    {
        return $this->getAlivePlayers()->hasPlayerWithStatus(PlayerStatusEnum::PARIAH);
    }
}
