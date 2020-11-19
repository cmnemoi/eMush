<?php

namespace Mush\Item\Entity\Items;

use Doctrine\ORM\Mapping as ORM;
use Mush\Item\Entity\Item;
use Mush\Item\Entity\ItemType;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Action\Enum\ActionEnum;

/**
 * @ORM\Entity()
 */
class Plant extends ItemType
{
    protected string $type = ItemTypeEnum::PLANT;

    protected array $actions = [ActionEnum::WATER_PLANT, ActionEnum::TREAT_PLANT, ActionEnum::HYBRIDIZE];

    /**
     * @ORM\OneToOne(targetEntity="Mush\Item\Entity\Item", inversedBy=")
     */
    private ?Item $fruit = null;

     /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $maturationTime = [];

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $minOxygen;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxOxygen;

    public function getFruit(): ?Item
    {
        return $this->fruit;
    }

    public function setFruit(Item $fruit): Plant
    {
        $this->fruit = $fruit;

        return $this;
    }

    public function getMaturationTime(): array
    {
        return $this->maturationTime;
    }

    public function setMaturationTime(array $maturationTime): Plant
    {
        $this->maturationTime = $maturationTime;

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
