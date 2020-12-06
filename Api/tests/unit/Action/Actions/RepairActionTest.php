<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Action;
use Mush\Action\Actions\Repair;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Service\SuccessRateServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RepairActionTest extends TestCase
{
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var GameItemServiceInterface | Mockery\Mock */
    private GameItemServiceInterface $itemService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var SuccessRateServiceInterface | Mockery\Mock */
    private SuccessRateServiceInterface $successRateService;
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
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
        $this->successRateService = Mockery::mock(SuccessRateServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $gameConfigService = Mockery::mock(GameConfigServiceInterface::class);
        $this->gameConfig = new GameConfig();
        $gameConfigService->shouldReceive('getConfig')->andReturn($this->gameConfig)->once();

        $eventDispatcher->shouldReceive('dispatch');

        $this->action = new Repair(
            $eventDispatcher,
            $this->roomLogService,
            $this->itemService,
            $this->playerService,
            $this->randomService,
            $this->successRateService,
            $this->statusService,
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
        $gameItem->setItem($item);
        $gameItem
            ->setRoom($room)
        ;

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($player, $actionParameter);

        //Not broken
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        $broken = new Status();
        $broken
            ->setName(ItemStatusEnum::BROKEN)
        ;

        //Not in the same room
        $gameItem
            ->addStatus($broken)
            ->setRoom(new Room())
        ;
        $room->removeItem($gameItem);

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        $daedalus = new Daedalus();
        $room = new Room();
        $gameItem = new GameItem();
        $item = new Item();
        $item
            ->setBreakableRate(10)
        ;

        $broken = new Status();
        $broken
            ->setName(ItemStatusEnum::BROKEN)
        ;

        $gameItem
            ->setItem($item)
            ->setRoom($room)
            ->addStatus($broken)
        ;

        $this->roomLogService->shouldReceive('createItemLog')->twice();

        $this->itemService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $attempt = new Attempt();
        $attempt
            ->setName(StatusEnum::ATTEMPT)
            ->setAction($this->action->getActionName())
        ;
        $this->statusService->shouldReceive('createAttemptStatus')->andReturn($attempt)->once();

        $player = $this->createPlayer($daedalus, $room);

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);

        $this->action->loadParameters($player, $actionParameter);

        $this->successRateService->shouldReceive('getSuccessRate')->andReturn(10)->once();
        $this->randomService->shouldReceive('randomPercent')->andReturn(100)->once();

        //Fail try
        $result = $this->action->execute();

        $this->assertInstanceOf(Fail::class, $result);
        $this->assertCount(1, $room->getItems()->first()->getStatuses());
        $this->assertCount(1, $player->getStatuses());
        $this->assertEquals(1, $attempt->getCharge());
        $this->assertEquals(9, $player->getActionPoint());

        $this->successRateService->shouldReceive('getSuccessRate')->andReturn(10)->once();
        $this->randomService->shouldReceive('randomPercent')->andReturn(1)->once();

        //Success
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getItems());
        $this->assertCount(0, $room->getItems()->first()->getStatuses());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(8, $player->getActionPoint());
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
