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
class Component extends ItemType
{
    protected string $type = ItemTypeEnum::COMPONENT;
}
