<?php

namespace Mush\Tests\unit\Action\Actions;

use Mush\Action\Actions\MotivationalSpeech;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;

/**
 * @internal
 */
final class MotivationalSpeechActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->createActionEntity(ActionEnum::MOTIVATIONAL_SPEECH);
        $this->actionConfig->setOutputQuantity(2);

        $this->actionHandler = new MotivationalSpeech(
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

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $speaker);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($listener);
        $this->eventService->shouldReceive('callEvent')->once();
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($speaker, $this->actionConfig, $this->actionProvider, null, ActionVariableEnum::OUTPUT_QUANTITY)
            ->andReturn(2)
            ->once();

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
