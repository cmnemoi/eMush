<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Move;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;

class MoveActionTest extends AbstractActionTest
{
    /** @var RoomLogServiceInterface|Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var PlayerServiceInterface|Mockery\Mock */
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
            $this->actionService,
            $this->validator,
            $this->playerService,
            $this->roomLogService,
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
        $roomStart = new Place();
        $roomEnd = new Place();
        $door = new Door();
        $door
            ->addRoom($roomStart)
            ->addRoom($roomEnd)
        ;
        $roomStart->addDoor($door);
        $roomEnd->addDoor($door);

        $this->roomLogService->shouldReceive('createLog')->times(4);
        $this->playerService->shouldReceive('persist');

        $player = $this->createPlayer(new Daedalus(), $roomStart);

        $this->action->loadParameters($this->actionEntity, $player, $door);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEquals($player->getPlace(), $roomEnd);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);

        $this->eventDispatcher->shouldReceive('dispatch')->times(3);
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEquals($player->getPlace(), $roomStart);
    }
}
