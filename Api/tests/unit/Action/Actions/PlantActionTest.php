<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Action;
use Mush\Action\Actions\Build;
use Mush\Action\Entity\ActionParameters;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Item;
use Mush\Item\Entity\Items\Plant;
use Mush\Item\Entity\Items\Fruit;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlantActionTest extends TestCase
{
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var GameItemServiceInterface | Mockery\Mock */
    private GameItemServiceInterface $itemService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
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

        $eventDispatcher->shouldReceive('dispatch');

        $this->action = new Build(
            $eventDispatcher,
            $this->roomLogService,
            $this->itemService,
            $this->playerService
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
        $gamItem = new GameItem();
        $item = new Item();
        $gameItem
                    ->setItem($item)
                    ->setRoom($room);
        
        $fruit = new Fruit();
        $fruit->setPlantName('plant');
        

        $plant = new Plant();
        $plant->setName('plant');
        
        
        $gameHydropot = new GameItem();
        $hydropot = new Item();
        $hydropot->setName(ItemEnum::HYDROPOT);
        $gameHydropot
                    ->setItem($hydropot)
                    ->setRoom($room)
                    ->setName(ItemEnum::HYDROPOT);     
        
        
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
               ->setIngredients('metal_scraps' => 2)
         $item->setTypes(new ArrayCollection([$blueprint]));
         
         $result = $this->action->execute();
         $this->assertInstanceOf(Error::class, $result);
    }    
    
    public function testExecute()
    {
        $room = new Room();
        $gameItem = new GameItem();
        $item = new Item();
        $gameItem
	        ->setItem($blueprint)
           ->setRoom($room)
        ;
        
        $product = new Item();
        
        
        $blueprint = new Blueprint();
        $blueprint 
               ->setIngredients('metal_scraps' => 1)
               ->setItem($product);
        $item->setTypes(new ArrayCollection([$blueprint]));
        

        $gameIngredient = new GameItem();
        $ingredient = new Item();
        $gameIngredient->setItem($gameIngredient);
        $ingredient->setName('metal_scraps');
        $gameIngredient->setRoom($room);
        
        
        
        $this->roomLogService->shouldReceive('createItemLog')->once();
        $this->itemService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $player = new Player();
        $player = $this->createPlayer(new Daedalus(), $room);


        $this->action->loadParameters($player, $actionParameter);

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
            ->addSkill(SkillEnum::TECHNICIAN)
            ->setDaedalus($daedalus)
            ->setRoom($room)
        ;
        return $player;
    }
    
}
