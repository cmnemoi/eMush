<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\GetUp;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class GetUpActionTest extends AbstractActionTest
{
    /** @var StatusServiceInterface|Mockery\Mock */
    private StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::GET_UP);

        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->action = new GetUp(
            $this->eventDispatcher,
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
        Mockery::close();
    }

    public function testExecute()
    {
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameItem = new GameEquipment();
        $item = new EquipmentConfig();
        $item
            ->setName(EquipmentEnum::BED)
        ;
        $gameItem
            ->setEquipment($item)
            ->setPlace($room)
            ->setName(EquipmentEnum::BED)
        ;

        $status = new Status($player);
        $status
            ->setName(PlayerStatusEnum::LYING_DOWN)
            ->setTarget($gameItem)
        ;

        $this->action->loadParameters($this->actionEntity, $player);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->statusService->shouldReceive('delete');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $player->getStatuses());
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $this->assertEquals(10, $player->getActionPoint());
    }
}
