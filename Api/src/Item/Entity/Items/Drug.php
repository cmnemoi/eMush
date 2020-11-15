<?php

namespace Mush\Item\Entity\Items;

use Doctrine\ORM\Mapping as ORM;
use Mush\Item\Enum\ItemTypeEnum;

/**
 * Class Item.
 *
 * @ORM\Entity
 */
class Drug extends Ration
{
    protected string $type = ItemTypeEnum::DRUG;

    protected ?bool $isPerishable = false;
    
    protected int $satiety = 0;
    
     /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $effectsNumber = [0];
    
    
    //@TODO more precision on the cure is needed (is the number of desease point remooved random)
    
    
    public function getEffectsNumber(): array
    {
        return $this->effectsNumber;
    }

    public function setEffectsNumber(array $effectsNumber): Fruit
    {
        $this->effectsNumber = $effectsNumber;

        return $this;
    }
}
