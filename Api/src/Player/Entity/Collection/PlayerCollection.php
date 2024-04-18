<?php

namespace Mush\Player\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Player\Entity\Player;

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
}
