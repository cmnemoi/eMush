<?php

namespace Mush\Tests\unit\Action\Actions;

use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Gag;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;

class GagActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::GAG, 1);

        $this->action = new Gag(
            $this->eventService,
            $this->actionService,
            $this->validator,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testExecute()
    {
        $room = new Place();
        $daedalus = new Daedalus();

        $player = $this->createPlayer($daedalus, $room);

        $player2 = $this->createPlayer($daedalus, $room);

        $this->action->loadParameters($this->actionEntity, $player, $player2);

        // No item in the room
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
