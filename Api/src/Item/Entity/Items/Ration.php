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
     * @ORM\Column(type="array", nullable=false)
     */
    private array $moralPoints = [0];
    
    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $actionPoints= [0];
    
    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $movementPoints = [0];
    
   /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $healthPoints= [0];

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
    
     /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $curesNumber = [0];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseasesNumber = [0];
    

    //Rations currently only have consume Action
    public function setActions(array $actions): Ration
    {
        return $this;
    }

    /**
     * @return array
     */
    public function getActions(): array
    {
        return [ActionEnum::CONSUME];
    }
    
    /**
     * @return array
     */
   public function getActionPoints(): array
    {
        return $this->actionPoints;
    }

    /**
     * @param array $ActionPoints
     * @return Ration
     */   
    public function setActionPoints(array $actionPoints): Ration
    {
        $this->actionPoints = $actionPoints;

        return $this;
    }
    
     /**
     * @return array
     */
    public function getMovementPoints(): array
    {
        return $this->movementPoints;
    }

    /**
     * @param array $MovementPoints
     * @return Ration
     */    
    public function setMovementPoints(array $movementPoints): Ration
    {
        $this->movementPoints = $movementPoints;

        return $this;
    }

    /**
     * @return array
     */
     public function getHealthPoints(): array
    {
        return $this->healthPoints;
    }
    
    /**
     * @param array $HealthPoints
     * @return Ration
     */
    public function setHealthPoints(array $healthPoints): Ration
    {
        $this->healthlPoints = $healthPoints;

        return $this;
    }
    
     /**
     * @return array
     */
     public function getMoralPoints(): array
    {
        return $this->moralPoints;
    }
    
    /**
     * @param array $moralPoints
     * @return Ration
     */
    public function setMoralPoints(array $moralPoints): Ration
    {
        $this->moralPoints = $moralPoints;

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
    
       public function getCuresNumber(): array
    {
        return $this->curesNumber;
    }
    
    public function setCuresNumber(array $curesNumber): Ration
    {
        $this->curesNumber = $curesNumber;

        return $this;
    }
    
       public function getDiseasesNumber(): array
    {
        return $this->diseasesNumber;
    }
    
    public function setDiseasesNumber(array $diseasesNumber): Ration
    {
        $this->diseasesNumber = $diseasesNumber;

        return $this;
    }
    
}
