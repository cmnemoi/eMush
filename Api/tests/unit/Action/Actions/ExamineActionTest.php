<?php

namespace Mush\Tests\unit\Action\Actions;

use Mush\Action\Actions\ReportEquipment;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;

/**
 * @internal
 */
final class ExamineActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createActionEntity(ActionEnum::EXAMINE, 0);

        $this->actionHandler = new ReportEquipment(
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

        $gameEquipment = new GameEquipment($room);
        $gameEquipment->setName('item');

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameEquipment);

        // No item in the room
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent')->once();

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
