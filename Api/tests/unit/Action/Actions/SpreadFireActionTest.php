<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\SpreadFire;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;

class SpreadFireActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::SPREAD_FIRE, 1);

        $this->action = new SpreadFire(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
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
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player);

        //No item in the room
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
