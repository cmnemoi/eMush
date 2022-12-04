<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\InsertFuel;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Place\Entity\Place;

class InsertFuelTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::INSERT_FUEL);

        $this->action = new InsertFuel(
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

    public function testExecute()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $player = $this->createPlayer($daedalus, $room);

        $gameItem = new GameItem($player);
        $item = new ItemConfig();
        $gameItem->setEquipment($item);

        $item->setName(ItemEnum::FUEL_CAPSULE);

        $gameItem->setName(ItemEnum::FUEL_CAPSULE);

        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setMaxFuel(32);

        $daedalus->setFuel(10);

        $tank = new EquipmentConfig();
        $tank->setName(EquipmentEnum::FUEL_TANK);

        $gameTank = new GameEquipment($room);
        $gameTank->setEquipment($tank)->setName(EquipmentEnum::FUEL_TANK);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch')->once();
        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $result = $this->action->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(1, $room->getEquipments());
        self::assertEquals(10, $player->getActionPoint());
    }
}
