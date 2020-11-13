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
    private array $effectsNumber = [0];
    
    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $extraEffect = [];
    
    
    
    public function getEffectsNumber(): array
    {
        return $this->effectsNumber;
    }

    public function setEffectsNumber(array $effectsNumber): Fruit
    {
        $this->effectsNumber = $effectsNumber;

        return $this;
    }
    
    public function getExtraEffect(): array
    {
        return $this->extraEffect;
    }

    public function setExtraEffect(array $extraEffect): Fruit
    {
        $this->extraEffect = $extraEffect;

        return $this;
}
