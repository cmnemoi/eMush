<?php


namespace Mush\Item\Entity\Items;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Item\Entity\Item;

/**
 * Class Item
 * @package Mush\Entity
 *
 * @ORM\Entity
 */
class BluePrint extends Tool
{
    protected string $type = ItemTypeEnum::BLUEPRINT;
    
    protected array $actions = [ActionEnum::BUILD];

    /**
     * @ORM\OneToOne(targetEntity="Mush\Item\Entity\Item", inversedBy=")
     */
    private ?item $item=null;
    
     /**
     * @ORM\Column(type="array", nullable=false)
     */
    private ?array $ingredients=null;

    public function getItem(): item
    {
        return $this->item;
    }

    public function setItem(item $item): Blueprint
    {
        $this->item = $item;
        return $this;
    }
    
    public function getIngredients(): array
    {
        return $this->ingredients;
    }

    public function setIngredients(array $ingredients): Blueprint
    {
        $this->ingredients = $ingredients;
        return $this;
    }
}
