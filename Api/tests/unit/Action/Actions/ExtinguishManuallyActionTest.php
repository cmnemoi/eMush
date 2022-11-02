<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\ExtinguishManually;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;

class ExtinguishManuallyActionTest extends AbstractActionTest
{
    private RandomServiceInterface|Mockery\Mock $randomService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::REPAIR, 1);

        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->action = new ExtinguishManually(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->randomService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testExecuteFail()
    {
        $room = new Place();
        $fire = new Status($room, new StatusConfig());

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getSuccessRate')->andReturn(0)->once();
        $this->randomService->shouldReceive('getSuccessThreshold')->andReturn(100)->once();
        $this->eventService->shouldReceive('callEvent')->once();

        // Fail try
        $result = $this->action->execute();

        $this->assertInstanceOf(Fail::class, $result);
        $this->assertCount(1, $room->getStatuses());
    }

    public function testExecuteSuccess()
    {
        $room = new Place();
        $fire = new Status($room, new StatusConfig());

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getSuccessRate')->andReturn(100)->once();
        $this->randomService->shouldReceive('getSuccessThreshold')->andReturn(0)->once();
        $this->eventService->shouldReceive('callEvent')->once();

        // Success
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
