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

    //Following variable change their signification compared to ration
    //The idea is that those variable store now all possible values of %, delayMin and delaylenght for any randomly picken disease
    //DiseasesChances: Store the chance (value) for a given % (key) to be picked as the chance to get cured or sick
    //DiseasesDelayMin: Store the chance (value) for a given DelayMin (key) to be picked
    //DiseasesDelayMin: Store the chance (value) for a given DelayLenght (key) to be picked
    //ExtraEffect: Store a list of extraEffect that can be randomly picked

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
}
