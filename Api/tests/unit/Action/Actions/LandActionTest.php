<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\Land;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\PatrolShip;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Service\PlayerServiceInterface;

/**
 * @internal
 */
final class LandActionTest extends AbstractActionTest
{
    /** @var Mockery\Mock|PlayerServiceInterface */
    private PlayerServiceInterface $playerService;

    /** @var Mockery\Mock|RandomServiceInterface */
    private RandomServiceInterface $randomService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->createActionEntity(ActionEnum::LAND, 2, 0);
        $this->actionConfig->setCriticalRate(20);

        $this->playerService = \Mockery::mock(PlayerServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);

        $this->actionHandler = new Land(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->playerService,
            $this->randomService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testExecuteFail()
    {
        $daedalus = new Daedalus();

        $roomStart = new Place();
        $roomStart->setDaedalus($daedalus);
        $roomStart->setName(RoomEnum::PASIPHAE);

        $roomEnd = new Place();
        $roomEnd->setDaedalus($daedalus);
        $roomEnd->setName(RoomEnum::ALPHA_BAY_2);

        $patrolShipMechanic = new PatrolShip();
        $patrolShipMechanic->setDockingPlace(RoomEnum::ALPHA_BAY_2);

        $patrollerConfig = new EquipmentConfig();
        $patrollerConfig->setName(EquipmentEnum::PASIPHAE);
        $patrollerConfig->setEquipmentName(EquipmentEnum::PASIPHAE);
        $patrollerConfig->setMechanics([$patrolShipMechanic]);

        $patroller = new GameEquipment($roomStart);
        $patroller->setName(EquipmentEnum::PASIPHAE);
        $patroller->setEquipment($patrollerConfig);

        $this->playerService->shouldReceive('persist');

        $player = $this->createPlayer($daedalus, $roomStart);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $patroller);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')->andReturn(100);
        $this->randomService->shouldReceive('isSuccessful')->with(100)->andReturn(false);
        $this->eventService->shouldReceive('callEvent')->times(1);

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertNotInstanceOf(CriticalSuccess::class, $result);
        self::assertSame($player->getPlace(), $roomEnd);
    }

    public function testExecuteSuccess()
    {
        $daedalus = new Daedalus();

        $roomStart = new Place();
        $roomStart->setDaedalus($daedalus);
        $roomStart->setName(RoomEnum::PASIPHAE);

        $roomEnd = new Place();
        $roomEnd->setDaedalus($daedalus);
        $roomEnd->setName(RoomEnum::ALPHA_BAY_2);

        $patrollerConfig = new EquipmentConfig();
        $patrollerConfig->setName(EquipmentEnum::PASIPHAE);
        $patrollerConfig->setEquipmentName(EquipmentEnum::PASIPHAE);
        $patrollerConfig->setMechanics([new PatrolShip()]);

        $patrolShipMechanic = new PatrolShip();
        $patrolShipMechanic->setDockingPlace(RoomEnum::ALPHA_BAY_2);

        $patrollerConfig = new EquipmentConfig();
        $patrollerConfig->setName(EquipmentEnum::PASIPHAE);
        $patrollerConfig->setEquipmentName(EquipmentEnum::PASIPHAE);
        $patrollerConfig->setMechanics([$patrolShipMechanic]);

        $patroller = new GameEquipment($roomStart);
        $patroller->setName(EquipmentEnum::PASIPHAE);
        $patroller->setEquipment($patrollerConfig);

        $this->playerService->shouldReceive('persist');

        $player = $this->createPlayer($daedalus, $roomStart);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $patroller);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')->andReturn(100);
        $this->randomService->shouldReceive('isSuccessful')->with(100)->andReturn(true);
        $this->eventService->shouldReceive('callEvent')->times(1);

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(CriticalSuccess::class, $result);
        self::assertSame($player->getPlace(), $roomEnd);
    }
}
