<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\RetrieveOxygen;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;

/**
 * @internal
 */
final class RetrieveOxygenTest extends AbstractActionTest
{
    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);
        $this->createActionEntity(ActionEnum::RETRIEVE_OXYGEN);

        $this->actionHandler = new RetrieveOxygen(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->gameEquipmentService
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
        $gameItem = new GameItem(new Place());
        $item = new ItemConfig();
        $gameItem->setEquipment($item);

        $item
            ->setEquipmentName(ItemEnum::OXYGEN_CAPSULE);

        $player = $this->createPlayer($daedalus, $room);
        $gameItem
            ->setName(ItemEnum::OXYGEN_CAPSULE);

        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setMaxOxygen(32)
            ->setInitOxygen(10);

        $daedalus->setDaedalusVariables($daedalusConfig);

        $tank = new EquipmentConfig();
        $tank->setActionConfigs(new ArrayCollection([$this->actionConfig]));

        $gameTank = new GameEquipment($room);
        $gameTank
            ->setEquipment($tank)
            ->setName(EquipmentEnum::OXYGEN_TANK)
            ->setHolder($room);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent')->once();
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameTank);

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(1, $room->getEquipments());
        self::assertSame(10, $player->getActionPoint());
    }
}
