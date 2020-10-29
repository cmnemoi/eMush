<?php

namespace Mush\Item\Entity\Items;

use Doctrine\ORM\Mapping as ORM;
use Mush\Item\Enum\ItemTypeEnum;

/**
 * Class Item
 * @package Mush\Entity
 *
 * @ORM\Entity
 */
class Document extends Tool
{
    protected string $type = ItemTypeEnum::DOCUMENT;
}
