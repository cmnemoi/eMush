<?php

namespace Mush\Tests\unit\Action\Actions;

use Mush\Action\Actions\InsertOxygen;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Place\Entity\Place;

/**
 * @internal
 */
final class InsertOxygenTest extends AbstractActionTest
{
    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createActionEntity(ActionEnum::INSERT_OXYGEN);

        $this->actionHandler = new InsertOxygen(
            $this->eventService,
            $this->actionService,
            $this->validator,
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testExecute()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $player = $this->createPlayer($daedalus, $room);

        $gameItem = new GameItem($player);
        $item = new ItemConfig();
        $gameItem->setEquipment($item);

        $item
            ->setEquipmentName(ItemEnum::OXYGEN_CAPSULE);

        $gameItem->setName(ItemEnum::OXYGEN_CAPSULE);

        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setMaxOxygen(32)->setInitOxygen(10);
        $daedalus->setDaedalusVariables($daedalusConfig);

        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setMaxOxygen(32);

        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $tank = new EquipmentConfig();
        $tank->setEquipmentName(EquipmentEnum::OXYGEN_TANK);
        $gameTank = new GameEquipment($room);
        $gameTank
            ->setEquipment($tank)
            ->setName(EquipmentEnum::OXYGEN_TANK)
            ->setHolder($room);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')->andReturn(1);
        $this->eventService->shouldReceive('callEvent')->once();
        $this->eventService->shouldReceive('callEvent')->once();

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(1, $room->getEquipments());
        self::assertSame(10, $player->getActionPoint());
    }
}
