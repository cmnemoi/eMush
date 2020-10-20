<?php


namespace Mush\Item\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;

/**
 * Class Item
 * @package Mush\Entity
 *
 * @ORM\Entity(repositoryClass="Mush\Item\Repository\ItemRepository")
 */
class Plant extends Item
{
    private GamePlant $gameFruit;
    private int $load;

    public function getGameFruit(): GamePlant
    {
        return $this->gameFruit;
    }

    public function setGameFruit(GamePlant $gameFruit): Plant
    {
        $this->gameFruit = $gameFruit;
        return $this;
    }

    public function getLoad(): int
    {
        return $this->load;
    }

    public function setLoad(int $load): Plant
    {
        $this->load = $load;
        return $this;
    }
}