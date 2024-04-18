<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\BoringSpeech;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Status\Service\StatusServiceInterface;

/**
 * @internal
 */
final class BoringSpeechActionTest extends AbstractActionTest
{
    private Mockery\Mock|StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::BORING_SPEECH);
        $this->actionEntity->setOutputQuantity(3);

        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->action = new BoringSpeech(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->statusService,
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
        $this->statusService->shouldReceive('createStatusFromName')->once();
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($speaker, $this->actionEntity, null, ActionVariableEnum::OUTPUT_QUANTITY)
            ->andReturn(3)
            ->once();

        $result = $this->action->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
