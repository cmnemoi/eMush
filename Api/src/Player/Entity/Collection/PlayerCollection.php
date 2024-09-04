<?php

namespace Mush\Player\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillEnum;

/**
 * @template-extends ArrayCollection<int, Player>
 */
class PlayerCollection extends ArrayCollection
{
    public function getPlayerAlive(): self
    {
        return $this->filter(static fn (Player $player) => $player->isAlive());
    }

    public function getPlayerDead(): self
    {
        return $this->filter(static fn (Player $player) => !$player->isAlive());
    }

    public function getMushPlayer(): self
    {
        return $this->filter(static fn (Player $player) => $player->isMush());
    }

    public function getHumanPlayer(): self
    {
        return $this->filter(static fn (Player $player) => !$player->isMush());
    }

    public function getPlayerByName(string $name): ?Player
    {
        return $this->filter(static fn (Player $player) => $player->getName() === $name)->first() ?: null;
    }

    public function getPlayersWithSkill(SkillEnum $skill): self
    {
        return $this->filter(static fn (Player $player) => $player->hasSkill($skill));
    }

    public function getAllExcept(Player $playerToExclude): self
    {
        return $this->filter(static fn (Player $player) => $player->notEquals($playerToExclude));
    }

    public function getActivePlayers(): self
    {
        return $this->getPlayerAlive()->filter(static fn (Player $player) => $player->isActive());
    }

    public function getNumberOfHumanAndAnonymushPlayers(): int
    {
        $anoymushPlayers = $this->getPlayersWithSkill(SkillEnum::ANONYMUSH)->toArray();
        $humanPlayers = $this->getHumanPlayer()->toArray();

        return \count(array_merge($humanPlayers, $anoymushPlayers));
    }

    public function getNumberOfVisibleMushPlayers(): int
    {
        return $this->getMushPlayer()->filter(static fn (Player $player) => $player->hasSkill(SkillEnum::ANONYMUSH) === false)->count();
    }

    public function hasPlayerWithSkill(SkillEnum $skill): bool
    {
        return $this->getPlayersWithSkill($skill)->count() > 0;
    }

    public function hasOneWithStatus(string $status): bool
    {
        return $this->filter(static fn (Player $player) => $player->hasStatus($status))->count() > 0;
    }

    public function getAllExceptMultiple(self $playersToExclude): self
    {
        return $this->filter(static fn (Player $player) => !$playersToExclude->contains($player));
    }

    public function getPlayerWithStatusOrThrow(string $status): Player
    {
        $player = $this->getPlayerWithStatus($status);

        if ($player === null) {
            throw new \RuntimeException('No player with status ' . $status);
        }

        return $player;
    }

    public function hasPlayerWithStatus(string $status): bool
    {
        return $this->getPlayerWithStatus($status) !== null;
    }

    private function getPlayerWithStatus(string $status): ?Player
    {
        return $this->filter(static fn (Player $player) => $player->hasStatus($status))->first() ?: null;
    }
}
