<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\RetrieveFuel;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Symfony\Contracts\EventDispatcher\Event;

class RetrieveFuelTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::RETRIEVE_FUEL, -1);

        $this->action = new RetrieveFuel(
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
        $item = new ItemConfig();

        $gameItem = new GameItem();
        $gameItem->setEquipment($item);
        $gameItem->setName(ItemEnum::FUEL_CAPSULE);

        $item->setName(ItemEnum::FUEL_CAPSULE);

        $player = $this->createPlayer($daedalus, $room);

        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setMaxFuel(32);

        $gameConfig = new GameConfig();
        $gameConfig->setMaxItemInInventory(3);
        $gameConfig->setDaedalusConfig($daedalusConfig);
        $daedalus->setGameConfig($gameConfig);

        $daedalus->setFuel(10);

        $tank = new EquipmentConfig();
        $tank->setActions(new ArrayCollection([$this->actionEntity]));

        $gameTank = new GameEquipment();
        $gameTank->setEquipment($tank)->setName(EquipmentEnum::FUEL_TANK)->setHolder($room);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent')
            ->withArgs(fn (Event $event) => (
                $event instanceof EquipmentEvent &&
                $event->getEquipment()->getName() === ItemEnum::FUEL_CAPSULE &&
                $event->getEquipment()->getHolder() === $player)
            )
            ->once();
        $this->eventService->shouldReceive('callEvent')->once();

        $this->action->loadParameters($this->actionEntity, $player, $gameTank);

        $result = $this->action->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(1, $room->getEquipments());
        self::assertEquals(10, $player->getActionPoint());
    }
}
