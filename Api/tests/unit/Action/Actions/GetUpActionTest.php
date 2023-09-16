<?php

namespace Mush\Tests\unit\Action\Actions;

use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\GetUp;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;

class GetUpActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::GET_UP);

        $this->action = new GetUp(
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

        $gameItem = new GameEquipment($room);
        $item = new EquipmentConfig();
        $item
            ->setEquipmentName(EquipmentEnum::BED)
        ;
        $gameItem
            ->setEquipment($item)
            ->setName(EquipmentEnum::BED)
        ;

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(PlayerStatusEnum::LYING_DOWN);
        $status = new Status($player, $statusConfig);
        $status
            ->setTarget($gameItem)
        ;

        $this->action->loadParameters($this->actionEntity, $player);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertEquals(10, $player->getActionPoint());
    }
}
