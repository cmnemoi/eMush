<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\RetrieveOxygen;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Room\Entity\Room;

class RetrieveOxygenTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var DaedalusServiceInterface | Mockery\Mock */
    private DaedalusServiceInterface $daedalusService;
    private GameConfig $gameConfig;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::RETRIEVE_OXYGEN);

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->daedalusService = Mockery::mock(DaedalusServiceInterface::class);
        $gameConfigService = Mockery::mock(GameConfigServiceInterface::class);
        $this->gameConfig = new GameConfig();
        $gameConfigService->shouldReceive('getConfig')->andReturn($this->gameConfig)->once();

        $this->action = new RetrieveOxygen(
             $this->eventDispatcher,
             $this->gameEquipmentService,
             $this->daedalusService,
             $gameConfigService
         );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testCannotExecute()
    {
        $daedalus = new Daedalus();
        $room = new Room();

        $player = $this->createPlayer($daedalus, $room);

        $daedalus->setOxygen(0);
        $this->gameConfig->setMaxOxygen(32);
        $this->gameConfig->setMaxItemInInventory(3);

        $tank = new EquipmentConfig();
        $tank->setName(EquipmentEnum::OXYGEN_TANK);
        $gameTank = new GameEquipment();
        $gameTank
            ->setEquipment($tank)
            ->setName(EquipmentEnum::OXYGEN_TANK)
            ->setRoom($room)
            ;

        $actionParameter = new ActionParameters();
        $actionParameter->setEquipment($gameTank);

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        //No more oxygen
        $this->gameEquipmentService->shouldReceive('isOperational')->andReturn(true)->once();
        $result = $this->action->execute();

        //Inventory full
        $this->gameConfig->setMaxItemInInventory(0);
        $daedalus->setOxygen(10);

        $this->gameEquipmentService->shouldReceive('isOperational')->andReturn(true)->once();
        $result = $this->action->execute();

        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        $daedalus = new Daedalus();
        $room = new Room();
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem->setEquipment($item);

        $item
            ->setName(ItemEnum::OXYGEN_CAPSULE)
            ->setIsHeavy(false)
        ;

        $player = $this->createPlayer($daedalus, $room);
        $gameItem
            ->setName(ItemEnum::OXYGEN_CAPSULE)
        ;

        $daedalus->setOxygen(10);
        $this->gameConfig->setMaxOxygen(32);
        $this->gameConfig->setMaxItemInInventory(3);

        $tank = new EquipmentConfig();
        $tank->setName(EquipmentEnum::OXYGEN_TANK);
        $gameTank = new GameEquipment();
        $gameTank
            ->setEquipment($tank)
            ->setName(EquipmentEnum::OXYGEN_TANK)
            ->setRoom($room)
            ;

        $this->gameEquipmentService->shouldReceive('persist');
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->andReturn($gameItem)->once();
        $this->gameEquipmentService->shouldReceive('isOperational')->andReturn(true)->once();
        $this->daedalusService->shouldReceive('changeOxygenLevel')->andReturn($daedalus)->once();

        $actionParameter = new ActionParameters();
        $actionParameter->setEquipment($gameTank);

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $player->getItems());
        $this->assertCount(1, $room->getEquipments());
        $this->assertEquals(10, $player->getActionPoint());
    }
}
