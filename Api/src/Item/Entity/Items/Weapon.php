<?php


namespace Mush\Item\Entity\Items;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;

/**
 * Class Item
 * @package Mush\Entity
 *
 * @ORM\Entity
 */
class Weapon extends Tool
{
    //Weapons currently have default attack Action
    public function setActions(array $actions): Weapon
    {
        return $this;
    }

    public function getActions(): array
    {
        return [ActionEnum::ATTACK];
    }
}
