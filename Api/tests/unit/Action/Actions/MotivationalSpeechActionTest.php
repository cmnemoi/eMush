<?php

namespace Mush\Tests\unit\Action\Actions;

use Mush\Action\Actions\MotivationalSpeech;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;

class MotivationalSpeechActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::MOTIVATIONAL_SPEECH);

        $this->action = new MotivationalSpeech(
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
        $daedalus = new Daedalus();

        $room = new Place();

        $speaker = $this->createPlayer($daedalus, $room);
        $listener = $this->createPlayer($daedalus, $room);

        $this->action->loadParameters($this->actionEntity, $speaker);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($listener);
        $this->eventService->shouldReceive('callEvent')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
