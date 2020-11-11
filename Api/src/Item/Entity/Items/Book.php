<?php

namespace Mush\Item\Entity\Items;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Item\Enum\ItemTypeEnum;

/**
 * Class Item.
 *
 * @ORM\Entity
 */
class Book extends Tool
{
    protected string $type = ItemTypeEnum::BOOK;

    protected array $actions = [ActionEnum::READ_BOOK];

    /**
     * @ORM\Column(type="string", nullable=false)
     */
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
