<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionCost;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Items\Blueprint;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;

class Build extends Action
{
    protected const NAME = ActionEnum::BUILD;

    private GameItem $item;

    private RoomLogServiceInterface $roomLogService;
    private GameItemServiceInterface $itemService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        RoomLogServiceInterface $roomLogService,
        GameItemServiceInterface $itemService,
        PlayerServiceInterface $playerService
    ) {
        $this->roomLogService = $roomLogService;
        $this->itemService = $itemService;
        $this->playerService = $playerService;
        $this->actionCost = new ActionCost();
        $this->actionCost->setActionPointCost(3);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (! $item = $actionParameters->getItem()) {
            throw new \InvalidArgumentException('Invalid item parameter');
        }
        $this->player = $player;
        $this->item = $item;
    }

    public function canExecute(): bool
    {
    	$blueprintType = $this->item->getItem()->getItemType(ItemTypeEnum::BLUEPRINT); 	
    	
    	//TODO add conditions on the ingredients
        return $this->item->getItem()->getItemType(ItemTypeEnum::>BLUEPRINT) !== null &&
                   $this->player->canReachItem($this->item) &&
                   foreach ($blueprintType->getIngredients() as $itemName => $number)
		                 {if ($this->player->getReachableItemByName(string $itemname)->count() < $number)
		                 {return false;
                       }
                    }
    }

    protected function applyEffects(): ActionResult
    {
        /** @var Blueprint $blueprintType */
        $blueprintType = $this->item->getItem()->getItemType(ItemTypeEnum::BLUEPRINT);
        

        
        // add the item in the player inventory or in the room if the inventory is full
        $blueprintObject=$blueprintType->getItem()->createGameItem();
        if($this->player->getItems()->count() < $this->gameConfig->getMaxItemInInventory()){
        	   $$blueprintObject->setPlayer($this->player);
        } else {
            $blueprintObject->setRoom($this->player->getRoom());
        }
        
		  // remove the used ingredients starting from the player inventory
        foreach ($blueprintType->getIngredients() as $itemName => $number)
          {for($number)
          {if($this->player->hasItemByName($itemName)){
          	// @FIXME change to a random choice of the item
          	$ingredient=$this->player->getItems()->filter(fn(GameItem $gameItem) => $gameItem->getName() === $itemName)->first()
          	$this->player->removeItem($ingredient)
          	$this->itemService->delete($ingredient);
          } else{
          	// @FIXME change to a random choice of the item
          	$ingredient=$this->room->getItems()->filter(fn(GameItem $gameItem) => $gameItem->getName() === $itemName)->first()
          	$ingredient->setRoom(null)
          	$this->itemService->delete($ingredient);
          }
          }
        }
		
		// remove the blueprint
		$this->item
            ->setRoom(null)
            ->setPlayer(null)
        ;
        $this->itemService->delete($this->item);
		
		
        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createPlayerLog(
            ActionEnum::BUILD,
            $this->player->getRoom(),
            $this->player,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }

    public function getActionName(): string
    {
        return self::NAME;
    }
}
