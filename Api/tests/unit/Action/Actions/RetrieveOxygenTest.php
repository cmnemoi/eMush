<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\RetrieveOxygen;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Equipment;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\EquipmentFactoryInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;

class RetrieveOxygenTest extends AbstractActionTest
{
    private EquipmentFactoryInterface|Mockery\Mock $gameEquipmentService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();
        $this->gameEquipmentService = Mockery::mock(EquipmentFactoryInterface::class);
        $this->actionEntity = $this->createActionEntity(ActionEnum::RETRIEVE_OXYGEN);

        $this->action = new RetrieveOxygen(
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

    public function testExecute()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $gameItem = new Item();
        $item = new ItemConfig();
        $gameItem->setConfig($item);

        $item
            ->setName(ItemEnum::OXYGEN_CAPSULE)
        ;

        $player = $this->createPlayer($daedalus, $room);
        $gameItem
            ->setName(ItemEnum::OXYGEN_CAPSULE)
        ;

        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setMaxOxygen(32);

        $gameConfig = new GameConfig();
        $gameConfig->setMaxItemInInventory(3);
        $gameConfig->setDaedalusConfig($daedalusConfig);
        $daedalus->setGameConfig($gameConfig);

        $daedalus->setOxygen(10);

        $tank = new EquipmentConfig();
        $tank->setActions(new ArrayCollection([$this->actionEntity]));

        $gameTank = new Equipment();
        $gameTank
            ->setConfig($tank)
            ->setName(EquipmentEnum::OXYGEN_TANK)
            ->setHolder($room)
        ;

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch')->twice();
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();

        $this->action->loadParameters($this->actionEntity, $player, $gameTank);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertEquals(10, $player->getActionPoint());
    }
}
