<?php


namespace Mush\Item\Entity\Items;

use Doctrine\ORM\Mapping as ORM;
use Mush\Item\Entity\ItemType;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Action\Enum\ActionEnum;

/**
 * Class Item
 * @package Mush\Entity
 *
 * @ORM\Entity
 */
class Dismountable extends ItemType
{
    protected string $type = ItemTypeEnum::DISMOUNTABLE;

    protected array $actions = [ActionEnum::DISASSEMBLE];
    
     /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $chancesSuccess=null;
    
     /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $actionCost=null;

      /**
     * @ORM\Column(type="array", nullable=false)
     */
    private ?array $products=null;
    
    

    public function getChancesSuccess(): int
    {
        return $this->chancesSuccess;
    }

    public function setChancesSuccess(int $chancesSuccess): Dismountable
    {
        $this->chancesSuccess = $chancesSuccess;
        return $this;
    }
    
    public function getActionCost(): int
    {
        return $this->actionCost;
    }

    public function setActionCost(int $actionCost): Dismountable
    {
        $this->actionCost = $actionCost;
        return $this;
    }
    
    public function getProducts(): array
    {
        return $this->products;
    }

    public function setProducts(array $products): Dismountable
    {
        $this->products = $products;
        return $this;
    }
}
