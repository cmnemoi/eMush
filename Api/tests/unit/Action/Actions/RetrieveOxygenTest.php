<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\RetrieveOxygen;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Event\DaedalusModifierEvent;
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

class RetrieveOxygenTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::RETRIEVE_OXYGEN);

        $this->action = new RetrieveOxygen(
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
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem->setEquipment($item);

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

        $gameTank = new GameEquipment();
        $gameTank
            ->setEquipment($tank)
            ->setName(EquipmentEnum::OXYGEN_TANK)
            ->setHolder($room)
        ;

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent')
            ->withArgs(fn (Event $event) => (
                $event instanceof EquipmentEvent &&
                $event->getEquipment()->getName() === ItemEnum::OXYGEN_CAPSULE &&
                $event->getEquipment()->getHolder() === $player)
            )
            ->once();
        $this->eventService->shouldReceive('callEvent')
            ->withArgs(fn (Event $event) => (
                $event instanceof DaedalusModifierEvent &&
                $event->getQuantity() === -1)
            )
            ->once();

        $this->action->loadParameters($this->actionEntity, $player, $gameTank);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertEquals(10, $player->getActionPoint());
    }
}
