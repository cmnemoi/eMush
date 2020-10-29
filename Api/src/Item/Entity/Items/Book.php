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
class Book extends Tool
{
    protected string $type = ItemTypeEnum::BOOK;

    protected array $actions = []; // @Todo: read action

    private string $skill;

    public function getSkill(): string
    {
        return $this->skill;
    }

    public function setSkill(string $skill): Book
    {
        $this->skill = $skill;
        return $this;
    }
}
