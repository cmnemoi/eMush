<?php

namespace Mush\Item\Entity\Items;

use Doctrine\ORM\Mapping as ORM;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Action\Enum\ActionEnum;

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
    private array $fruitEffectsNumber = [0];

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
    private array $diseasesEffectDelayLengh = [];


    public function getFruitEffectsNumber(): array
    {
        return $this->fruitEffectsNumber;
    }

    public function setFruitEffectsNumber(array $fruitEffectsNumber): Fruit
    {
        $this->fruitEffectsNumber = $fruitEffectsNumber;

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

    public function getDiseasesEffectChance(): array
    {
        return $this->diseasesEffectChance;
    }

    public function setDiseasesEffectChance(array $diseasesEffectChance): Fruit
    {
        $this->diseasesEffectChance = $diseasesEffectChance;

        return $this;
    }

    public function getDiseasesEffectDelayMin(): array
    {
        return $this->diseasesEffectDelayMin;
    }

    public function setDiseasesEffectDelayMin(array $diseasesEffectDelayMin): Fruit
    {
        $this->diseasesEffectDelayMin = $diseasesEffectDelayMin;

        return $this;
    }

    public function getDiseasesEffectDelayLengh(): array
    {
        return $this->diseasesEffectDelayLengh;
    }

    public function setDiseasesEffectDelayLengh(array $diseasesEffectDelayLengh): Fruit
    {
        $this->diseasesEffectDelayLengh = $diseasesEffectDelayLengh;

        return $this;
    }
}
