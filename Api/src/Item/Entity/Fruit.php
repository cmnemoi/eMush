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
class Fruit extends Item
{
    private GameFruit $gameFruit;

    public function getGameFruit(): GameFruit
    {
        return $this->gameFruit;
    }

    public function setGameFruit(GameFruit $gameFruit): Fruit
    {
        $this->gameFruit = $gameFruit;
        return $this;
    }
}