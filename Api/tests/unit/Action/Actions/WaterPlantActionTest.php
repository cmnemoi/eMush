<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\WaterPlant;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\GameItem;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Config\Mechanics\Plant;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;

class WaterPlantActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface|Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::WATER_PLANT, 1);

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);

        $this->action = new WaterPlant(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->gameEquipmentService
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

        $thirsty = new Status($gameItem);
        $thirsty
            ->setName(EquipmentStatusEnum::PLANT_THIRSTY)
        ;

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gameEquipmentService->shouldReceive('persist');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $this->assertCount(0, $player->getStatuses());
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

        $driedOut = new Status($gameItem);
        $driedOut
            ->setName(EquipmentStatusEnum::PLANT_DRY)
        ;

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gameEquipmentService->shouldReceive('persist');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $this->assertCount(0, $player->getStatuses());
    }
}
