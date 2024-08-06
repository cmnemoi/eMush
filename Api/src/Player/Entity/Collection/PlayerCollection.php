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
        $player = $this->filter(static fn (Player $player) => $player->getName() === $name)->first();
        if (!$player) {
            return null;
        }

        return $player;
    }

    public function getPlayersWithSkill(SkillEnum $skill): self
    {
        return $this->filter(static fn (Player $player) => $player->hasSkill($skill));
    }

    public function getAllExcept(Player $playerToExclude): self
    {
        return $this->filter(static fn (Player $player) => $player->getId() !== $playerToExclude->getId());
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
}
