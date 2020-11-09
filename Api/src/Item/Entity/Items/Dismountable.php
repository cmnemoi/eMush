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
class Dismountable extends Tool
{
    protected string $type = ItemTypeEnum::DISMOUNTABLE;

    protected array $actions = [ActionEnum::DISMANTLE];
    
     /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $chancesSucces=null;
    
     /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $actionCost=null;

        
    private ?array $products=null;
    
    

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
