<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Action;
use Mush\Action\Actions\Build;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Action\Entity\ActionParameters;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Item;
use Mush\Item\Entity\Items\Blueprint;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BuildActionTest extends TestCase
{
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var GameItemServiceInterface | Mockery\Mock */
    private GameItemServiceInterface $itemService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
    
    private GameConfig $gameConfig;
    private Action $action;

    /**
     * @before
     */
    public function before()
    {
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->itemService = Mockery::mock(GameItemServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $gameConfigService = Mockery::mock(GameConfigServiceInterface::class);
        $this->gameConfig = new GameConfig();
        $gameConfigService->shouldReceive('getConfig')->andReturn($this->gameConfig)->once();
        
        
        $eventDispatcher->shouldReceive('dispatch');

        $this->action = new Build(
            $eventDispatcher,
            $this->roomLogService,
            $this->itemService,
            $this->playerService,
            $gameConfigService
        );
    }

        
    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }


    public function testCannotExecute()
    {
        $room = new Room();
        $gameItem = new GameItem();
        $item = new Item();
        $item ->setName('blueprint');
        $gameItem
                    ->setItem($item)
                    ->setRoom($room)
                    ->setName('blueprint');
     
        $product=new Item();
        
        $blueprint = new Blueprint();
        $blueprint 
               ->setIngredients(['metal_scraps' => 1])
               ->setItem($product);
        
        
        $gameIngredient = new GameItem();
        $ingredient = new Item();
        $ingredient->setName('metal_scraps');
        $gameIngredient
                 ->setItem($ingredient)
                 ->setRoom($room)
                 ->setName('metal_scraps');
        
        
        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($player, $actionParameter);
        
        
        //Not a blueprint
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);   
        
        $item->setTypes(new ArrayCollection([$blueprint]));
        
        //Ingredient in another room
        $gameIngredient->setRoom(new Room());
        
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
        
        //Not enough of a given ingredient
         $gameIngredient->setRoom($room);
         $blueprint 
               ->setIngredients(['metal_scraps' => 2]);
         $item->setTypes(new ArrayCollection([$blueprint]));
         
         $result = $this->action->execute();
         $this->assertInstanceOf(Error::class, $result);
    }    
    
    public function testExecute()
    {
        $room = new Room();
        $gameItem = new GameItem();
        $item = new Item();
        $item->setName('blueprint');
        $gameItem
	        ->setItem($item)
           ->setRoom($room)
           ->setName('blueprint')
        ;
        
        $product = new Item();
        $product->setName('product');
        $gameProduct = new GameItem();
        $gameProduct
               ->setItem($product)
               ->setName('product');
        
        
        
        $blueprint = new Blueprint();
        $blueprint 
               ->setIngredients(['metal_scraps' => 1])
               ->setItem($product);
        $item->setTypes(new ArrayCollection([$blueprint]));
        

        $gameIngredient = new GameItem();
        $ingredient = new Item();
        $ingredient->setName('metal_scraps');
        $gameIngredient
		        ->setItem($ingredient)
		        ->setRoom($room)
		        ->setName('metal_scraps');
        
        
        


        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $player = new Player();
        $player = $this->createPlayer(new Daedalus(), $room);


        $this->action->loadParameters($player, $actionParameter);


        $this->gameConfig->setMaxItemInInventory(3);
        $this->itemService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');
        
        $this->itemService->shouldReceive('createGameItem')->andReturn($gameProduct)->once();
        $this->itemService->shouldReceive('delete');
        $this->roomLogService->shouldReceive('createPlayerLog')->once();
 
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEmpty($player->getRoom()->getItems());
        $this->assertEquals($player->getItems()->first()->getItem(),$product);

    }
    
    private function createPlayer(Daedalus $daedalus, Room $room): Player
    {
        $player = new Player();
        $player
            ->setActionPoint(10)
            ->setMovementPoint(10)
            ->setMoralPoint(10)
            ->setDaedalus($daedalus)
            ->setRoom($room)
        ;
        return $player;
    }
    
}
