<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\WaterPlant;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;

class WaterPlantActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::WATER_PLANT, 1);

        $this->action = new WaterPlant(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testExecuteThirsty()
    {
        $room = new Place();

        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem
              ->setEquipment($item)
              ->setHolder($room)
        ;

        $plant = new Plant();
        $plant->addAction($this->actionEntity);
        $item->setMechanics(new ArrayCollection([$plant]));

        $statusConfig = new StatusConfig();
        $statusConfig->setName(EquipmentStatusEnum::PLANT_THIRSTY);
        $thirsty = new Status($gameItem, $statusConfig);

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
    }

    public function testExecuteDried()
    {
        $room = new Place();

        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem
            ->setEquipment($item)
            ->setHolder($room)
        ;

        $plant = new Plant();
        $plant->addAction($this->actionEntity);
        $item->setMechanics(new ArrayCollection([$plant]));

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $statusConfig = new StatusConfig();
        $statusConfig->setName(EquipmentStatusEnum::PLANT_DRY);
        $thirsty = new Status($gameItem, $statusConfig);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
    }
}
