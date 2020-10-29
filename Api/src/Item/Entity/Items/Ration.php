<?php


namespace Mush\Item\Entity\Items;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Item\Entity\ItemType;
use Mush\Item\Enum\ItemTypeEnum;

/**
 * @ORM\Entity()
 */
class Ration extends ItemType
{
    protected string $type = ItemTypeEnum::RATION;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $minActionPoint;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $maxActionPoint;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $minMovementPoint;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $maxMovementPoint;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $minHealthPoint;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $maxHealthPoint;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private int $minMoralPoint;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private int $maxMoralPoint;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $satiety = 1;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $cures = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseases = [];

    //Rations currently only have consume Action
    public function setActions(array $actions): Weapon
    {
        return $this;
    }

    public function getActions(): array
    {
        return [ActionEnum::CONSUME];
    }

    public function getMinActionPoint(): int
    {
        return $this->minActionPoint;
    }

    public function setMinActionPoint(int $minActionPoint): Ration
    {
        $this->minActionPoint = $minActionPoint;
        return $this;
    }

    public function getMaxActionPoint(): int
    {
        return $this->maxActionPoint;
    }

    public function setMaxActionPoint(int $maxActionPoint): Ration
    {
        $this->maxActionPoint = $maxActionPoint;
        return $this;
    }

    public function getMinMovementPoint(): int
    {
        return $this->minMovementPoint;
    }

    public function setMinMovementPoint(int $minMovementPoint): Ration
    {
        $this->minMovementPoint = $minMovementPoint;
        return $this;
    }

    public function getMaxMovementPoint(): int
    {
        return $this->maxMovementPoint;
    }

    public function setMaxMovementPoint(int $maxMovementPoint): Ration
    {
        $this->maxMovementPoint = $maxMovementPoint;
        return $this;
    }

    /**
     * @return int
     */
    public function getMinHealthPoint(): int
    {
        return $this->minHealthPoint;
    }

    /**
     * @param int $minHealthPoint
     * @return Ration
     */
    public function setMinHealthPoint(int $minHealthPoint): Ration
    {
        $this->minHealthPoint = $minHealthPoint;
        return $this;
    }

    public function getMaxHealthPoint(): int
    {
        return $this->maxHealthPoint;
    }

    public function setMaxHealthPoint(int $maxHealthPoint): Ration
    {
        $this->maxHealthPoint = $maxHealthPoint;
        return $this;
    }

    public function getMinMoralPoint(): int
    {
        return $this->minMoralPoint;
    }

    public function setMinMoralPoint(int $minMoralPoint): Ration
    {
        $this->minMoralPoint = $minMoralPoint;
        return $this;
    }

    public function getMaxMoralPoint(): int
    {
        return $this->maxMoralPoint;
    }

    public function setMaxMoralPoint(int $maxMoralPoint): Ration
    {
        $this->maxMoralPoint = $maxMoralPoint;
        return $this;
    }

    public function getSatiety(): int
    {
        return $this->satiety;
    }

    public function setSatiety(int $satiety): Ration
    {
        $this->satiety = $satiety;
        return $this;
    }

    public function getCures(): array
    {
        return $this->cures;
    }

    public function setCures(array $cures): Ration
    {
        $this->cures = $cures;
        return $this;
    }

    public function getDiseases(): array
    {
        return $this->diseases;
    }

    public function setDiseases(array $diseases): Ration
    {
        $this->diseases = $diseases;
        return $this;
    }
}
