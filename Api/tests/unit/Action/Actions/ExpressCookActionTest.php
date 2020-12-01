<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Action;
use Mush\Action\Actions\ExpressCook;
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
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ItemStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ExpressCookActionTest extends TestCase
{
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var GameItemServiceInterface | Mockery\Mock */
    private GameItemServiceInterface $gameItemService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
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
        $this->gameItemService = Mockery::mock(GameItemServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $eventDispatcher->shouldReceive('dispatch');
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $gameConfigService = Mockery::mock(GameConfigServiceInterface::class);
        $this->gameConfig = new GameConfig();
        $gameConfigService->shouldReceive('getConfig')->andReturn($this->gameConfig)->once();

        $this->action = new ExpressCook(
            $eventDispatcher,
            $this->roomLogService,
            $this->gameItemService,
            $this->playerService,
            $this->statusService,
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
        $daedalus = new Daedalus();
        $room = new Room();

        $gameRation = new GameItem();
        $ration = new Item();
        $ration->setName('ration');
        $gameRation
            ->setItem($ration)
            ->setRoom($room)
            ->setName('ration')
        ;

        $chargeStatus = new ChargeStatus();
        $chargeStatus
             ->setName(ItemStatusEnum::CHARGES)
             ->setCharge(3);

        $gameMicrowave = new GameItem();
        $microwave = new Item();
        $microwave->setName(ToolItemEnum::MICROWAVE);
        $gameMicrowave
            ->setItem($microwave)
            ->setName(ToolItemEnum::MICROWAVE)
            ->setRoom($room)
            ->addStatus($chargeStatus)
        ;

        $chargeStatus->setGameItem($gameMicrowave);

        $player = $this->createPlayer(new Daedalus(), $room);
        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameRation);
        $this->action->loadParameters($player, $actionParameter);

        //not possible to cook (not frozen nor standard ration)
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        $frozenStatus = new Status();
        $frozenStatus
             ->setName(ItemStatusEnum::FROZEN)
             ->setGameItem($gameRation);
        $gameRation->addStatus($frozenStatus);

        $gameMicrowave->setRoom(null);
        //No microwave in the room
        $this->gameItemService->shouldReceive('getOperationalItemsByName')->andReturn(new ArrayCollection([]))->once();
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        //frozen fruit
        $daedalus = new Daedalus();
        $room = new Room();

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameRation = new GameItem();
        $ration = new Item();
        $ration->setName('ration');
        $gameRation
            ->setItem($ration)
            ->setPlayer($player)
            ->setName('ration')
        ;

        $frozenStatus = new Status();
        $frozenStatus
             ->setName(ItemStatusEnum::FROZEN)
             ->setGameItem($gameRation);
        $gameRation->addStatus($frozenStatus);

        $chargeStatus = new ChargeStatus();
        $chargeStatus
             ->setName(ItemStatusEnum::CHARGES)
             ->setCharge(3);

        $gameMicrowave = new GameItem();
        $microwave = new Item();
        $microwave->setName(ToolItemEnum::MICROWAVE);
        $gameMicrowave
            ->setItem($microwave)
            ->setName(ToolItemEnum::MICROWAVE)
            ->setRoom($room)
            ->addStatus($chargeStatus)
        ;
        $chargeStatus->setGameItem($gameMicrowave);

        $this->gameConfig->setMaxItemInInventory(3);
        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameRation);
        $this->action->loadParameters($player, $actionParameter);

        $this->gameItemService->shouldReceive('getOperationalItemsByName')->andReturn(new ArrayCollection([$gameMicrowave]))->twice();
        $this->roomLogService->shouldReceive('createItemLog')->once();
        $this->gameItemService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');
        $this->statusService->shouldReceive('persist');
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getItems());
        $this->assertCount(1, $player->getItems());
        $this->assertCount(1, $room->getItems()->first()->getStatuses());
        $this->assertCount(0, $player->getItems()->first()->getStatuses());
        $this->assertEquals(2, $room->getItems()->first()->getStatuses()->first()->getCharge());
        $this->assertEquals($gameRation->getName(), $player->getItems()->first()->getName());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(10, $player->getActionPoint());

        //Standard Ration
        $daedalus = new Daedalus();
        $room = new Room();

        $gameRation = new GameItem();
        $ration = new Item();
        $ration->setName(GameRationEnum::STANDARD_RATION);
        $gameRation
            ->setItem($ration)
            ->setRoom($room)
            ->setName(GameRationEnum::STANDARD_RATION)
        ;

        $chargeStatus = new ChargeStatus();
        $chargeStatus
             ->setName(ItemStatusEnum::CHARGES)
             ->setCharge(3);

        $gameMicrowave = new GameItem();
        $microwave = new Item();
        $microwave->setName(ToolItemEnum::MICROWAVE);
        $gameMicrowave
            ->setItem($microwave)
            ->setName(ToolItemEnum::MICROWAVE)
            ->setRoom($room)
            ->addStatus($chargeStatus)
        ;
        $chargeStatus->setGameItem($gameMicrowave);

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->gameConfig->setMaxItemInInventory(3);
        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameRation);
        $this->action->loadParameters($player, $actionParameter);

        $gameCookedRation = new GameItem();
        $cookedRation = new Item();
        $cookedRation
             ->setName(GameRationEnum::COOKED_RATION)
         ;
        $gameCookedRation
            ->setItem($cookedRation)
            ->setName(GameRationEnum::COOKED_RATION)
        ;

        $this->gameItemService->shouldReceive('delete');
        $this->gameItemService->shouldReceive('getOperationalItemsByName')->andReturn(new ArrayCollection([$gameMicrowave]))->twice();
        $this->gameItemService->shouldReceive('createGameItemFromName')->andReturn($gameCookedRation)->once();
        $this->roomLogService->shouldReceive('createItemLog')->once();
        $this->gameItemService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getItems());
        $this->assertCount(1, $player->getItems());
        $this->assertCount(1, $room->getItems()->first()->getStatuses());
        $this->assertCount(0, $player->getItems()->first()->getStatuses());
        $this->assertEquals(2, $room->getItems()->first()->getStatuses()->first()->getCharge());
        $this->assertEquals($gameCookedRation->getName(), $player->getItems()->first()->getName());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(10, $player->getActionPoint());
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
