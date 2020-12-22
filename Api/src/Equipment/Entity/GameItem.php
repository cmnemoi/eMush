<?php

namespace Mush\Equipment\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Player\Entity\Player;

/**
 * Class GameItem.
 *
 * @ORM\Entity
 */
class GameItem extends GameEquipment
{
    /**
     * @ORM\ManyToOne (targetEntity="Mush\Player\Entity\Player", inversedBy="items")
     */
    private ?Player $player = null;

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    /**
     * @return static
     */
    public function setPlayer(?Player $player): GameItem
    {
        if ($player !== $this->getPlayer()) {
            $oldPlayer = $this->getPlayer();

            $this->player = $player;

            if ($player !== null) {
                $player->addItem($this);
            }

            if ($oldPlayer !== null) {
                $oldPlayer->removeItem($this);
            }
        }

        if (null === $player && null !== $this->player) {
            $this->player->removeItem($this);
        }

        if ($player !== null && $this->player !== $player) {
            $player->addItem($this);
        }

        $this->player = $player;

        return $this;
    }

    /**
     * @return static
     */
    public function removeLocation(): GameItem
    {
        $this->setRoom(null);
        $this->setPlayer(null);

        return $this;
    }
}
