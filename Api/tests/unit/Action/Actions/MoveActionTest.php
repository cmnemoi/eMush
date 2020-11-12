<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Action;
use Mush\Action\Actions\Move;
use Mush\Action\Actions\Take;
use Mush\Action\Entity\ActionParameters;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\StatusEnum;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Item;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Item\Entity\Door;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Status;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MoveActionTest extends TestCase
{
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
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
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);

        $eventDispatcher->shouldReceive('dispatch');

        $this->action = new Move(
            $eventDispatcher,
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
            ->setName(StatusEnum::BROKEN)
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

        $this->action->loadParameters($player, $actionParameter);

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


        $this->roomLogService->shouldReceive('createPlayerLog')->times(4);
        $this->playerService->shouldReceive('persist');

        $actionParameter = new ActionParameters();
        $actionParameter->setDoor($door);
        $player = new Player();
        $player
            ->setActionPoint(10)
            ->setMovementPoint(10)
            ->setMoralPoint(10)
            ->setRoom($roomStart)
        ;

        $this->action->loadParameters($player, $actionParameter);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEquals($player->getRoom(), $roomEnd);
        $this->assertEmpty($roomStart->getPlayers());
        $this->assertEquals($player->getMovementPoint(), 9);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEquals($player->getRoom(), $roomStart);
        $this->assertEmpty($roomEnd->getPlayers());
        $this->assertEquals($player->getMovementPoint(), 8);
    }
}
