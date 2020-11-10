<?php

namespace Mush\Item\Entity\Items;

use Doctrine\ORM\Mapping as ORM;
use Mush\Item\Enum\ItemTypeEnum;

/**
 * Class Item
 * @package Mush\Entity
 *
 * @ORM\Entity
 */
class Drug extends Ration
{
    protected string $type = ItemTypeEnum::DRUG;
    
    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $moralPoints = [];
    
    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $actionPoints= [];
    
    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $movementPoints = [];
   
    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $cureDiseases = [];
    
    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $maxCuredDiseases = null;
    
    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $minCuredDiseases = null;
    
    
    //@TODO more precision on the cure is needed (is the number of desease point remooved random)
    
    
    public function getMoralPoints(): array
    {
        return $this->moralPoints;
    }
    
    public function setMoralPoints(array $moralPoints): Drug
    {
        $this->moralPoints = $moralPoints;

        return $this;
    }
    
    
    public function getActionPointEffect(): array
    {
        return $this->actionPointEffect;
    }
    
    public function setActionPoints(array $actionPoints): Drug
    {
        $this->actionPoints = $actionPoints;

        return $this;
    }
    
    
    public function getMovementPoints(): array
    {
        return $this->movementPoints;
    }
    
    public function setMovementPoints(array $movementPoints): Drug
    {
        $this->movementPoints = $movementPoints;

        return $this;
    }
    
    
    public function getCureDiseases(): array
    {
        return $this->cureDiseases;
    }
    
    public function setCureDiseases(array $cureDiseases): Drug
    {
        $this->cureDiseases = $cureDiseases;

        return $this;
    }
    
    
    public function getMaxCuredDiseases(): int
    {
        return $this->maxCuredDiseases;
    }
    
    public function setMaxCuredDiseases(int $maxCuredDiseases): Drug
    {
        $this->maxCuredDiseases = $maxCuredDiseases;

        return $this;
    }
    
    public function getMinCuredDiseases(): int
    {
        return $this->minCuredDiseases;
    }
    
    public function setMinCuredDiseases(int $minCuredDiseases): Drug
    {
        $this->minCuredDiseases = $minCuredDiseases;

        return $this;
    }
}
