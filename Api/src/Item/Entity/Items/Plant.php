<?php


namespace Mush\Item\Entity\Items;

use Doctrine\ORM\Mapping as ORM;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Item;
use Mush\Item\Enum\PlantStatusEnum;

/**
 * @ORM\Entity()
 */
class Plant extends Item
{
    /**
     * @ORM\OneToOne(targetEntity="Mush\Item\Entity\Items\Fruit", inversedBy=")
     */
    private ?Fruit $fruit = null;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxMaturationTime;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $minMaturationTime;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $minOxygen;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxOxygen;

    public function createGameItem(): GameItem
    {
        $gamePlant = parent::createGameItem();
        $gamePlant->setStatuses([PlantStatusEnum::YOUNG]); //New plants are always young

        return $gamePlant;
    }

    public function getFruit(): ?Fruit
    {
        return $this->fruit;
    }

    public function setFruit(Fruit $fruit): Plant
    {
        $this->fruit = $fruit;

        return $this;
    }

    public function getMaxMaturationTime(): int
    {
        return $this->maxMaturationTime;
    }

    public function setMaxMaturationTime(int $maxMaturationTime): Plant
    {
        $this->maxMaturationTime = $maxMaturationTime;
        return $this;
    }

    public function getMinMaturationTime(): int
    {
        return $this->minMaturationTime;
    }

    public function setMinMaturationTime(int $minMaturationTime): Plant
    {
        $this->minMaturationTime = $minMaturationTime;
        return $this;
    }

    public function getMinOxygen(): int
    {
        return $this->minOxygen;
    }

    public function setMinOxygen(int $minOxygen): Plant
    {
        $this->minOxygen = $minOxygen;
        return $this;
    }

    public function getMaxOxygen(): int
    {
        return $this->maxOxygen;
    }

    public function setMaxOxygen(int $maxOxygen): Plant
    {
        $this->maxOxygen = $maxOxygen;
        return $this;
    }
}
