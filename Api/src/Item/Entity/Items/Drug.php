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

    //@TODO more precision on the cure is needed (is the number of desease point remooved random)
}
