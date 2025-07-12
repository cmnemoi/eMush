<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\Land;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\PatrolShipManoeuvreServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\SpaceShip;
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
            $this->createStub(PatrolShipManoeuvreServiceInterface::class),
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

        $patrollerConfig = new EquipmentConfig();
        $patrollerConfig->setName(EquipmentEnum::PASIPHAE);
        $patrollerConfig->setEquipmentName(EquipmentEnum::PASIPHAE);

        $patroller = new SpaceShip($roomStart);
        $patroller->setDockingPlace(RoomEnum::ALPHA_BAY_2);
        $patroller->setName(EquipmentEnum::PASIPHAE);
        $patroller->setEquipment($patrollerConfig);

        $this->playerService->shouldReceive('persist');

        $player = $this->createPlayer($daedalus, $roomStart);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $patroller);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')->andReturn(100);
        $this->randomService->shouldReceive('isSuccessful')->with(100)->andReturn(false);
        $this->playerService->shouldReceive('changePlace')->once();

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertNotInstanceOf(CriticalSuccess::class, $result);
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

        $patroller = new SpaceShip($roomStart);
        $patroller->setName(EquipmentEnum::PASIPHAE);
        $patroller->setDockingPlace(RoomEnum::ALPHA_BAY_2);
        $patroller->setEquipment($patrollerConfig);

        $this->playerService->shouldReceive('persist');

        $player = $this->createPlayer($daedalus, $roomStart);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $patroller);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')->andReturn(100);
        $this->randomService->shouldReceive('isSuccessful')->with(100)->andReturn(true);
        $this->playerService->shouldReceive('changePlace')->once();

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(CriticalSuccess::class, $result);
    }
}
