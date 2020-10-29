<?php


namespace Mush\Item\Entity\Items;

use Doctrine\ORM\Mapping as ORM;
use Mush\Item\Entity\ItemType;
use Mush\Item\Enum\ItemTypeEnum;

/**
 * Class Item
 * @package Mush\Entity
 *
 * @ORM\Entity
 */
class Tool extends ItemType
{
    protected string $type = ItemTypeEnum::TOOL;
}
