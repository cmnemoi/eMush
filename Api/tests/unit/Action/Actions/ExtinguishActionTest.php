<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Extinguish;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\SuccessRateServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\Room\Service\RoomServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class ExtinguishActionTest extends AbstractActionTest
{
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var RoomServiceInterface | Mockery\Mock */
    private RoomServiceInterface $roomService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var SuccessRateServiceInterface | Mockery\Mock */
    private SuccessRateServiceInterface $successRateService;
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::REPAIR, 1);

        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->roomService = Mockery::mock(roomServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $this->successRateService = Mockery::mock(SuccessRateServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->action = new Extinguish(
            $this->eventDispatcher,
            $this->roomLogService,
            $this->playerService,
            $this->randomService,
            $this->successRateService,
            $this->statusService,
            $this->roomService
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
        $item = new ItemConfig();
        $gameItem->setEquipment($item);
        $gameItem
            ->setRoom($room)
        ;

        $action = new Action();
        $action->setName(ActionEnum::EXTINGUISH);
        $item->setActions(new ArrayCollection([$action]));

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        //No fire
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        $fire = new Status($room);
        $fire
            ->setName(StatusEnum::FIRE)
        ;

        //extinguisher is broken
        $broken = new Status($gameItem);
        $broken
            ->setName(EquipmentStatusEnum::BROKEN)
        ;

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        $room = new Room();
        $fire = new Status($room);
        $fire
            ->setName(StatusEnum::FIRE)
        ;

        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem->setEquipment($item);
        $gameItem
            ->setRoom($room)
        ;

        $action = new Action();
        $action->setName(ActionEnum::EXTINGUISH);
        $item->setActions(new ArrayCollection([$action]));

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);

        $this->roomLogService->shouldReceive('createActionLog')->twice();

        $this->roomService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $player = $this->createPlayer(new Daedalus(), $room);

        $attempt = new Attempt(new Player());
        $attempt
            ->setName(StatusEnum::ATTEMPT)
            ->setAction($this->action->getActionName())
        ;
        $this->statusService->shouldReceive('createAttemptStatus')->andReturn($attempt)->once();

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $this->successRateService->shouldReceive('getSuccessRate')->andReturn(10)->once();
        $this->randomService->shouldReceive('isSuccessfull')->andReturn(false)->once();

        //Fail try
        $result = $this->action->execute();

        $this->assertInstanceOf(Fail::class, $result);
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $this->assertCount(1, $room->getStatuses());
        $this->assertEquals(1, $attempt->getCharge());
        $this->assertEquals(9, $player->getActionPoint());

        $this->successRateService->shouldReceive('getSuccessRate')->andReturn(10)->once();
        $this->randomService->shouldReceive('isSuccessfull')->andReturn(true)->once();

        //Success
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $this->assertCount(0, $room->getStatuses());
        $this->assertEquals(8, $player->getActionPoint());
    }
}
