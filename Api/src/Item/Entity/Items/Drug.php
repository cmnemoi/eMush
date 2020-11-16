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

    protected bool $isPerishable = false;

    protected int $satiety = 0;

     /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $drugEffectsNumber = [];


    //@TODO more precision on the cure is needed (is the number of desease point remooved random)


    public function getDrugEffectsNumber(): array
    {
        return $this->drugEffectsNumber;
    }

    public function setDrugEffectsNumber(array $drugEffectsNumber): Fruit
    {
        $this->drugEffectsNumber = $drugEffectsNumber;

        return $this;
    }
}
