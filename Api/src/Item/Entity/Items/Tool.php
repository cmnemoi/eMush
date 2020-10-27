<?php


namespace Mush\Item\Entity\Items;

use Doctrine\ORM\Mapping as ORM;
use Mush\Item\Entity\Item;

/**
 * Class Item
 * @package Mush\Entity
 *
 * @ORM\Entity
 */
class Tool extends Item
{
    protected array $actions;

    /**
     * @return array
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @param array $actions
     * @return Tool
     */
    public function setActions(array $actions): Tool
    {
        $this->actions = $actions;
        return $this;
    }
}
