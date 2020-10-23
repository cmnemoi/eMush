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
    /**
     * @ORM\OneToOne(targetEntity="Mush\Item\Entity\GamePlant")
     */
    private GamePlant $gamePlant;

    /**
     * @ORM\Column(type="integer")
     */
    private int $charge = 0;

    public function getGamePlant(): GamePlant
    {
        return $this->gamePlant;
    }

    public function setGamePlant(GamePlant $gamePlant): Plant
    {
        $this->gamePlant = $gamePlant;
        return $this;
    }

    public function getCharge(): int
    {
        return $this->charge;
    }

    public function setCharge(int $charge): Plant
    {
        $this->charge = $charge;
        return $this;
    }

    public function isMature(): bool
    {
        return $this->gamePlant->getMaturationTime() <= $this->getCharge();
    }
}
