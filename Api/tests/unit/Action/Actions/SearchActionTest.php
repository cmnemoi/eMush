<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Action;
use Mush\Action\Actions\Search;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Service\SuccessRateServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Item;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ItemStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SearchActionTest extends TestCase
{
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var GameItemServiceInterface | Mockery\Mock */
    private GameItemServiceInterface $itemService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var SuccessRateServiceInterface | Mockery\Mock */
    private SuccessRateServiceInterface $successRateService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
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
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $eventDispatcher->shouldReceive('dispatch');

        $this->action = new Search(
            $eventDispatcher,
            $this->roomLogService,
            $this->itemService,
            $this->playerService,
            $this->statusService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testExecute()
    {
        $room = new Room();

        $player = $this->createPlayer(new Daedalus(), $room);
        $actionParameter = new ActionParameters();
        $this->action->loadParameters($player, $actionParameter);

        //No item in the room
        $this->roomLogService->shouldReceive('createPlayerLog')->once();
        $result = $this->action->execute();
        $this->assertInstanceOf(Fail::class, $result);

        //No hidden item in the room
        $gameItem = new GameItem();
        $item = new Item();
        $gameItem
            ->setItem($item)
            ->setRoom($room)
        ;

        $this->roomLogService->shouldReceive('createPlayerLog')->once();
        $result = $this->action->execute();
        $this->assertInstanceOf(Fail::class, $result);

        //Success find
        $room = new Room();
        $gameItem = new GameItem();
        $item = new Item();
        $gameItem
            ->setItem($item)
            ->setRoom($room)
        ;

        $hidden = new Status();
        $hiddenBy = $this->createPlayer(new Daedalus(), new Room());
        $hidden
            ->setName(ItemStatusEnum::HIDDEN)
            ->setPlayer($hiddenBy)
            ->setGameItem($gameItem)
        ;
        $gameItem->addStatus($hidden);
        $hiddenBy->addStatus($hidden);

        $player = $this->createPlayer(new Daedalus(), $room);
        $actionParameter = new ActionParameters();
        $this->action->loadParameters($player, $actionParameter);

        $this->statusService->shouldReceive('getMostRecent')->andReturn($gameItem)->once();
        $this->roomLogService->shouldReceive('createItemLog')->once();
        $this->itemService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getItems());
        $this->assertCount(0, $room->getItems()->first()->getStatuses());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(9, $player->getActionPoint());
        $this->assertCount(0, $hiddenBy->getStatuses());

        //2 hidden items
        $room = new Room();
        $gameItem = new GameItem();
        $item = new Item();
        $gameItem
            ->setItem($item)
            ->setRoom($room)
        ;

        $hiddenBy = $this->createPlayer(new Daedalus(), new Room());
        $hidden = new Status();
        $hidden
            ->setName(ItemStatusEnum::HIDDEN)
            ->setPlayer($hiddenBy)
            ->setGameItem($gameItem)
        ;
        $gameItem->addStatus($hidden);
        $hiddenBy->addStatus($hidden);

        $gameItem2 = new GameItem();
        $gameItem2
            ->setItem($item)
            ->setRoom($room)
        ;

        $hidden2 = new Status();
        $hidden2
            ->setName(ItemStatusEnum::HIDDEN)
            ->setPlayer($hiddenBy)
            ->setGameItem($gameItem2)
        ;
        $gameItem2->addStatus($hidden2);
        $hiddenBy->addStatus($hidden2);

        $player = $this->createPlayer(new Daedalus(), $room);
        $actionParameter = new ActionParameters();
        $this->action->loadParameters($player, $actionParameter);

        $this->statusService->shouldReceive('getMostRecent')->andReturn($gameItem)->once();
        $this->roomLogService->shouldReceive('createItemLog')->once();
        $this->itemService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(2, $room->getItems());
        $this->assertCount(0, $room->getItems()->first()->getStatuses());
        $this->assertCount(1, $room->getItems()->last()->getStatuses());
        $this->assertEquals($hidden2, $hiddenBy->getStatuses()->first());
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
