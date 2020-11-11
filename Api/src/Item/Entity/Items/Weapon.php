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
class Weapon extends Tool
{
    protected string $type = ItemTypeEnum::WEAPON;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    public int $maxCharges = 0;

    //Weapons currently have default attack Action
    public function setActions(array $actions): Weapon
    {
        return $this;
    }

    public function getActions(): array
    {
        return [ActionEnum::ATTACK];
    }

    public function getMaxCharges(): int
    {
        return $this->maxCharges;
    }

    public function setMaxCharges(int $maxCharges): Weapon
    {
        $this->maxCharges = $maxCharges;

        return $this;
    }
}
