<?php


namespace Mush\Item\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Item\Entity\Items\Ration;

/**
 * Class ConsumableEffect
 * @package Mush\Item\Entity
 *
 * @ORM\Entity
 */
class ConsumableEffect
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Daedalus\Entity\Daedalus")
     */
    private Daedalus $daedalus;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Item\Entity\Items\Ration")
     */
    private Ration $ration;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private int $actionPoint=0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private int $movementPoint=0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private int $healthPoint=0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private int $moralPoint=0;


     /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $cures = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseases = [];
    
    

    public function getId(): int
    {
        return $this->id;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(Daedalus $daedalus): ConsumableEffect
    {
        $this->daedalus = $daedalus;
        return $this;
    }

    public function getRation(): Ration
    {
        return $this->ration;
    }

    public function setRation(Ration $ration): ConsumableEffect
    {
        $this->ration = $ration;
        return $this;
    }

    public function getActionPoint(): int
    {
        return $this->actionPoint;
    }

    public function setActionPoint(int $actionPoint): ConsumableEffect
    {
        $this->actionPoint = $actionPoint;
        return $this;
    }

    public function getMovementPoint(): int
    {
        return $this->movementPoint;
    }

    public function setMovementPoint(int $movementPoint): ConsumableEffect
    {
        $this->movementPoint = $movementPoint;
        return $this;
    }

    public function getHealthPoint(): int
    {
        return $this->healthPoint;
    }

    public function setHealthPoint(int $healthPoint): ConsumableEffect
    {
        $this->healthPoint = $healthPoint;
        return $this;
    }

    public function getMoralPoint(): int
    {
        return $this->moralPoint;
    }

    public function setMoralPoint(int $moralPoint): ConsumableEffect
    {
        $this->moralPoint = $moralPoint;
        return $this;
    }
    
    public function getCures(): array
    {
        return $this->cures;
    }

    public function setCures(array $cures): ConsumableEffect
    {
        $this->cures = $cures;
        return $this;
    }

    public function getDiseases(): array
    {
        return $this->diseases;
    }

    public function setDiseases(array $diseases): ConsumableEffect
    {
        $this->diseases = $diseases;
        return $this;
    }
}
