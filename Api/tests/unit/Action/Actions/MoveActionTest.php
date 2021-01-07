<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Move;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;

class MoveActionTest extends AbstractActionTest
{
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::MOVE, 0, 1);

        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);

        $this->action = new Move(
            $this->eventDispatcher,
            $this->playerService,
            $this->roomLogService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testCanExecute()
    {
        $roomStart = new Room();
        $roomEnd = new Room();
        $door = new Door();
        $broken = new Status();
        $broken
            ->setName(EquipmentStatusEnum::BROKEN)
        ;

        $door
            ->addRoom($roomStart)
            ->addRoom($roomEnd)
        ;

        $roomStart->addDoor($door);
        $roomEnd->addDoor($door);

        $actionParameter = new ActionParameters();
        $actionParameter->setDoor($door);
        $player = new Player();
        $player
            ->setMoralPoint(10)
            ->setRoom($roomStart)
        ;

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        //No movement point
        $player
            ->setActionPoint(0)
            ->setMovementPoint(0)
        ;
        $result = $this->action->execute();

        $this->assertInstanceOf(Error::class, $result);

        //Door is broken
        $player->setMovementPoint(1);
        $door->addStatus($broken);

        $result = $this->action->execute();

        $this->assertInstanceOf(Error::class, $result);
        $this->assertEquals($player->getMovementPoint(), 1);

        //Player is in other room
        $player
            ->setRoom(new Room())
            ->removeStatus($broken)
        ;

        $this->assertInstanceOf(Error::class, $result);
        $this->assertEquals($player->getMovementPoint(), 1);
    }

    public function testExecute()
    {
        $roomStart = new Room();
        $roomEnd = new Room();
        $door = new Door();
        $door
            ->addRoom($roomStart)
            ->addRoom($roomEnd)
        ;
        $roomStart->addDoor($door);
        $roomEnd->addDoor($door);

        $this->roomLogService->shouldReceive('createActionLog')->times(4);
        $this->playerService->shouldReceive('persist');

        $actionParameter = new ActionParameters();
        $actionParameter->setDoor($door);
        $player = $this->createPlayer(new Daedalus(), $roomStart);

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEquals($player->getRoom(), $roomEnd);
        $this->assertEquals($player->getMovementPoint(), 9);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEquals($player->getRoom(), $roomStart);
        $this->assertEquals($player->getMovementPoint(), 8);
    }
}
