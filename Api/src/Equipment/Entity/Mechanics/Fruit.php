<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Fruit extends Ration
{
    protected string $mechanic = EquipmentMechanicEnum::FRUIT;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $plantName;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $fruitEffectsNumber = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseasesName = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseasesEffectChance = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseasesEffectDelayMin = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseasesEffectDelayLength = [];

    public function getPlantName(): string
    {
        return $this->plantName;
    }

    /**
     * @return static
     */
    public function setPlantName(string $plantName): Fruit
    {
        $this->plantName = $plantName;

        return $this;
    }

    public function getFruitEffectsNumber(): array
    {
        return $this->fruitEffectsNumber;
    }

    /**
     * @return static
     */
    public function setFruitEffectsNumber(array $fruitEffectsNumber): Fruit
    {
        $this->fruitEffectsNumber = $fruitEffectsNumber;

        return $this;
    }

    public function getDiseasesName(): array
    {
        return $this->diseasesName;
    }

    /**
     * @return static
     */
    public function setDiseasesName(array $diseasesName): Fruit
    {
        $this->diseasesName = $diseasesName;

        return $this;
    }

    public function getDiseasesEffectChance(): array
    {
        return $this->diseasesEffectChance;
    }

    /**
     * @return static
     */
    public function setDiseasesEffectChance(array $diseasesEffectChance): Fruit
    {
        $this->diseasesEffectChance = $diseasesEffectChance;

        return $this;
    }

    public function getDiseasesEffectDelayMin(): array
    {
        return $this->diseasesEffectDelayMin;
    }

    /**
     * @return static
     */
    public function setDiseasesEffectDelayMin(array $diseasesEffectDelayMin): Fruit
    {
        $this->diseasesEffectDelayMin = $diseasesEffectDelayMin;

        return $this;
    }

    public function getDiseasesEffectDelayLength(): array
    {
        return $this->diseasesEffectDelayLength;
    }

    /**
     * @return static
     */
    public function setDiseasesEffectDelayLength(array $diseasesEffectDelayLength): Fruit
    {
        $this->diseasesEffectDelayLength = $diseasesEffectDelayLength;

        return $this;
    }
}
