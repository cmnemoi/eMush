<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\InsertFuel;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;

class InsertFuelTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var DaedalusServiceInterface | Mockery\Mock */
    private DaedalusServiceInterface $daedalusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::INSERT_FUEL);

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->daedalusService = Mockery::mock(DaedalusServiceInterface::class);

        $this->action = new InsertFuel(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->gameEquipmentService,
            $this->daedalusService,
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
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem->setEquipment($item);

        $item->setName(ItemEnum::FUEL_CAPSULE)->setIsHeavy(false);

        $player = $this->createPlayer($daedalus, $room);
        $gameItem->setName(ItemEnum::FUEL_CAPSULE)->setPlayer($player);

        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setMaxFuel(32);

        $gameConfig = new GameConfig();
        $gameConfig->setMaxItemInInventory(3);
        $gameConfig->setDaedalusConfig($daedalusConfig);

        $daedalus->setGameConfig($gameConfig);
        $daedalus->setFuel(10);

        $tank = new EquipmentConfig();
        $tank->setName(EquipmentEnum::FUEL_TANK);

        $gameTank = new GameEquipment();
        $gameTank->setEquipment($tank)->setName(EquipmentEnum::FUEL_TANK)->setPlace($room);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gameEquipmentService->shouldReceive('delete');
        $this->daedalusService->shouldReceive('changeFuelLevel')->andReturn($daedalus);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $result = $this->action->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertEmpty($player->getItems());
        self::assertCount(1, $room->getEquipments());
        self::assertEquals(10, $player->getActionPoint());
    }
}
