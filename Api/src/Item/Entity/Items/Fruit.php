<?php

namespace Mush\Item\Entity\Items;

use Doctrine\ORM\Mapping as ORM;
use Mush\Item\Enum\ItemTypeEnum;

/**
 * Class Item.
 *
 * @ORM\Entity
 */
class Fruit extends Ration
{
    protected string $type = ItemTypeEnum::FRUIT;

     /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $plantEffectsNumber = [0];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseasesName = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseaseEffectChance = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseaseDelayMin = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseaseDelayLengh = [];


    public function getPlantEffectsNumber(): array
    {
        return $this->plantEffectsNumber;
    }

    public function setPlantEffectsNumber(array $plantEffectsNumber): Fruit
    {
        $this->plantEffectsNumber = $plantEffectsNumber;

        return $this;
    }

    public function getDiseasesName(): array
    {
        return $this->diseasesName;
    }

    public function setDiseasesName(array $diseasesName): Fruit
    {
        $this->diseasesName = $diseasesName;

        return $this;
    }

    public function getDiseaseEffectChance(): array
    {
        return $this->diseaseEffectChance;
    }

    public function setDiseaseEffectChance(array $diseaseEffectChance): Fruit
    {
        $this->diseaseEffectChance = $diseaseEffectChance;

        return $this;
    }

    public function getDiseaseDelayMin(): array
    {
        return $this->diseasesDelayMin;
    }

    public function setDiseasesDelayMin(array $diseaseDelayMin): Fruit
    {
        $this->diseaseDelayMin = $diseaseDelayMin;

        return $this;
    }

    public function getDiseaseDelayLengh(): array
    {
        return $this->diseaseDelayLengh;
    }

    public function setDiseaseDelayLengh(array $diseaseDelayLengh): Fruit
    {
        $this->diseaseDelayLengh = $diseaseDelayLengh;

        return $this;
    }
}
