<?php

namespace Mush\Tests\unit\Action\Actions;

use Mush\Action\Actions\MakeSick;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Place\Entity\Place;

class MakeSickActionTest extends AbstractActionTest
{
    private DiseaseCauseServiceInterface|Mockery\Mock $diseaseCauseService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::MAKE_SICK);
        $this->diseaseCauseService = \Mockery::mock(DiseaseCauseServiceInterface::class);

        $this->action = new MakeSick(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->diseaseCauseService
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

        $this->eventService->shouldReceive('callEvent');

        $player = $this->createPlayer(new Daedalus(), $room);
        $playerTarget = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $playerTarget);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->diseaseCauseService->shouldReceive('handleDiseaseForCause');
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
