<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\WaterPlant;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class WaterPlantActionTest extends AbstractActionTest
{
    /* @var StatusServiceInterface|Mockery\Mock */
    private StatusServiceInterface|Mockery\Mock $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::WATER_PLANT, 1);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->action = new WaterPlant(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->statusService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testExecuteThirsty()
    {
        $room = new Place();

        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $gameItem
              ->setEquipment($item)
            ->setName('plant')
        ;

        $plant = new Plant();
        $plant->addAction($this->actionEntity);
        $item->setMechanics(new ArrayCollection([$plant]));

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(EquipmentStatusEnum::PLANT_THIRSTY);
        $thirsty = new Status($gameItem, $statusConfig);

        $player = $this->createPlayer(new Daedalus(), $room);

        $room->setDaedalus($player->getDaedalus());

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->statusService->shouldReceive('removeStatus')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
    }

    public function testExecuteDried()
    {
        $room = new Place();

        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $gameItem
            ->setEquipment($item)
            ->setName('plant')
        ;

        $plant = new Plant();
        $plant->addAction($this->actionEntity);
        $item->setMechanics(new ArrayCollection([$plant]));

        $player = $this->createPlayer(new Daedalus(), $room);

        $room->setDaedalus($player->getDaedalus());

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(EquipmentStatusEnum::PLANT_DRY);
        $thirsty = new Status($gameItem, $statusConfig);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->statusService->shouldReceive('removeStatus')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
    }
}
