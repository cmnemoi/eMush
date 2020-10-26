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
 * @ORM\Entity(repositoryClass="Mush\Item\Repository\GameItemRepository")
 */
class GamePlant extends GameItem
{
    /**
     * @ORM\OneToOne(targetEntity="Mush\Item\Entity\Plant")
     */
    private Plant $plant;

    /**
     * @ORM\Column(type="integer")
     */
    private int $charge = 0;

    public function getPlant(): Plant
    {
        return $this->plant;
    }

    public function setPlant(Plant $plant): GamePlant
    {
        $this->plant = $plant;
        return $this;
    }

    public function getCharge(): int
    {
        return $this->charge;
    }

    public function setCharge(int $charge): GamePlant
    {
        $this->charge = $charge;
        return $this;
    }

    public function isMature(): bool
    {
        return $this->plant->getMaturationTime() <= $this->getCharge();
    }
}
