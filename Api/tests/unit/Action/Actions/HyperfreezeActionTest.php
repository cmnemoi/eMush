<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Action;
use Mush\Action\Actions\Hyperfreeze;
use Mush\Action\Entity\ActionParameters;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Item;
use Mush\Item\Entity\Items\Ration;
use Mush\Item\Enum\GameRationEnum;
use Mush\Item\Enum\ToolItemEnum;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class HyperfreezeActionTest extends TestCase
{
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var GameItemServiceInterface | Mockery\Mock */
    private GameItemServiceInterface $gameItemService;
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
        $this->gameItemService = Mockery::mock(GameItemServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $eventDispatcher->shouldReceive('dispatch');

        $gameConfigService = Mockery::mock(GameConfigServiceInterface::class);
        $this->gameConfig = new GameConfig();
        $gameConfigService->shouldReceive('getConfig')->andReturn($this->gameConfig)->once();

        $this->action = new Hyperfreeze(
            $eventDispatcher,
            $this->roomLogService,
            $this->gameItemService,
            $this->playerService,
            $gameConfigService,
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

        $gameRation = new GameItem();
        $ration = new Item();
        $ration->setName('ration');
        $gameRation
            ->setItem($ration)
            ->setRoom($room)
            ->setName('ration')
        ;

        $gameSuperfreezer = new GameItem();
        $superfreezer = new Item();
        $superfreezer->setName(ToolItemEnum::SUPERFREEZER);
        $gameSuperfreezer
            ->setItem($superfreezer)
            ->setName(ToolItemEnum::SUPERFREEZER)
            ->setRoom($room)
        ;

        $player = $this->createPlayer(new Daedalus(), $room);
        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameRation);
        $this->action->loadParameters($player, $actionParameter);

        //Not a ration
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        $rationType = new Ration();
        $rationType->setIsPerishable(false);
        $ration->setTypes(new ArrayCollection([$rationType]));

        //not perishable
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        $rationType->setIsPerishable(true);
        $gameSuperfreezer->setRoom(null);
        //No superfreezer in the room
        $this->gameItemService->shouldReceive('getOperationalItemsByName')->andReturn(new ArrayCollection([]))->once();
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        //fruit
        $daedalus = new Daedalus();
        $room = new Room();

        $player = $this->createPlayer(new Daedalus(), $room);

        $rationType = new Ration();
        $rationType->setIsPerishable(true);

        $gameRation = new GameItem();
        $ration = new Item();
        $ration
             ->setTypes(new ArrayCollection([$rationType]))
             ->setName('fruit')
         ;
        $gameRation
            ->setItem($ration)
            ->setRoom($room)
            ->setName('fruit')
        ;

        $gameSuperfreezer = new GameItem();
        $superfreezer = new Item();
        $superfreezer->setName(ToolItemEnum::SUPERFREEZER);
        $gameSuperfreezer
            ->setItem($superfreezer)
            ->setName(ToolItemEnum::SUPERFREEZER)
            ->setRoom($room)
        ;

        $this->gameConfig->setMaxItemInInventory(3);
        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameRation);
        $this->action->loadParameters($player, $actionParameter);

        $this->gameItemService->shouldReceive('getOperationalItemsByName')->andReturn(new ArrayCollection([$gameSuperfreezer]))->once();
        $this->roomLogService->shouldReceive('createItemLog')->once();
        $this->gameItemService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(2, $room->getItems());
        $this->assertCount(0, $player->getItems());
        $this->assertCount(1, $room->getItems()->first()->getStatuses());
        $this->assertEquals($gameRation->getName(), $room->getItems()->first()->getName());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(9, $player->getActionPoint());

        //Alien Steak
        $daedalus = new Daedalus();
        $room = new Room();

        $player = $this->createPlayer(new Daedalus(), $room);

        $rationType = new Ration();
        $rationType->setIsPerishable(true);

        $gameRation = new GameItem();
        $ration = new Item();
        $ration
             ->setTypes(new ArrayCollection([$rationType]))
             ->setName(GameRationEnum::ALIEN_STEAK)
         ;
        $gameRation
            ->setItem($ration)
            ->setRoom($room)
            ->setName(GameRationEnum::ALIEN_STEAK)
        ;

        $gameSuperfreezer = new GameItem();
        $superfreezer = new Item();
        $superfreezer->setName(ToolItemEnum::SUPERFREEZER);
        $gameSuperfreezer
            ->setItem($superfreezer)
            ->setName(ToolItemEnum::SUPERFREEZER)
            ->setRoom($room)
        ;

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameRation);
        $this->action->loadParameters($player, $actionParameter);

        $gameStandardRation = new GameItem();
        $standardRation = new Item();
        $standardRation
             ->setName(GameRationEnum::STANDARD_RATION)
         ;
        $gameStandardRation
            ->setItem($standardRation)
            ->setName(GameRationEnum::STANDARD_RATION)
        ;

        $this->gameItemService->shouldReceive('delete');
        $this->gameItemService->shouldReceive('getOperationalItemsByName')->andReturn(new ArrayCollection([$gameSuperfreezer]))->once();
        $this->gameItemService->shouldReceive('createGameItemFromName')->andReturn($gameStandardRation)->once();
        $this->roomLogService->shouldReceive('createItemLog')->once();
        $this->gameItemService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getItems());
        $this->assertCount(1, $player->getItems());
        $this->assertCount(0, $player->getItems()->first()->getStatuses());
        $this->assertCount(0, $room->getItems()->first()->getStatuses());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(9, $player->getActionPoint());
        $this->assertEquals($gameStandardRation, $player->getItems()->first());
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
