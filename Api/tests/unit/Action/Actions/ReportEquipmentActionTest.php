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
final class ReportEquipmentActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::REPORT_EQUIPMENT, 1);

        $this->action = new ReportEquipment(
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

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment->setName('equipment');

        $this->action->loadParameters($this->actionEntity, $player, $gameEquipment);

        // No item in the room
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent')->once();

        $result = $this->action->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
