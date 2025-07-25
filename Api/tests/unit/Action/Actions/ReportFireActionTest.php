<?php

namespace Mush\Tests\unit\Action\Actions;

use Mush\Action\Actions\ReportFire;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;

/**
 * @internal
 */
final class ReportFireActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createActionEntity(ActionEnum::REPORT_FIRE, 1);

        $this->actionHandler = new ReportFire(
            $this->eventService,
            $this->actionService,
            $this->validator,
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testExecute()
    {
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player);

        // No item in the room
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent')->once();

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
