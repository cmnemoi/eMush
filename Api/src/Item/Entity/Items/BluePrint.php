<?php


namespace Mush\Item\Entity\Items;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Item\Enum\ItemTypeEnum;

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
     * @ORM\Column(type="string", nullable=false)
     */
    private ?item $item=null;
    
    private ?array $ingredients=null;

    public function getItem(): item
    {
        return $this->item;
    }

    public function setItem(array $ingredients): Blueprint
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
