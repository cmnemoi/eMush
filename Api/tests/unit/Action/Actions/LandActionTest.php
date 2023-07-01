<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Land;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\PatrolShip;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;

class LandActionTest extends AbstractActionTest
{
    /** @var PlayerServiceInterface|Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var PlaceServiceInterface|Mockery\Mock */
    private PlaceServiceInterface $placeService;
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var RoomLogServiceInterface|Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::LAND, 2, 0);
        $this->actionEntity->setCriticalRate(20);

        $this->playerService = \Mockery::mock(PlayerServiceInterface::class);
        $this->placeService = \Mockery::mock(PlaceServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->roomLogService = \Mockery::mock(RoomLogServiceInterface::class);

        $this->action = new Land(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->playerService,
            $this->placeService,
            $this->randomService,
            $this->roomLogService
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
        $roomEnd = new Place();
        $roomEnd->setDaedalus($daedalus);
        $patrollerConfig = new EquipmentConfig();
        $patrollerConfig->setName(EquipmentEnum::PATROL_SHIP);
        $patrollerConfig->setEquipmentName(EquipmentEnum::PATROL_SHIP);
        $patrollerConfig->setMechanics([new PatrolShip()]);

        $patroller = new GameEquipment($roomStart);
        $patroller->setName(EquipmentEnum::PATROL_SHIP);
        $patroller->setEquipment($patrollerConfig);

        $this->playerService->shouldReceive('persist');

        $player = $this->createPlayer($daedalus, $roomStart);

        $this->action->loadParameters($this->actionEntity, $player, $patroller);

        $this->placeService->shouldReceive('findByNameAndDaedalus')->andReturn($roomEnd);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->randomService->shouldReceive('randomPercent')->andReturn(100);
        $this->eventService->shouldReceive('callEvent')->times(1);

        $result = $this->action->execute();

        $this->assertInstanceOf(Fail::class, $result);
        $this->assertEquals($player->getPlace(), $roomEnd);
    }

    public function testExecuteSuccess()
    {
        $daedalus = new Daedalus();

        $roomStart = new Place();
        $roomStart->setDaedalus($daedalus);
        $roomEnd = new Place();
        $roomEnd->setDaedalus($daedalus);

        $patrollerConfig = new EquipmentConfig();
        $patrollerConfig->setName(EquipmentEnum::PATROL_SHIP);
        $patrollerConfig->setEquipmentName(EquipmentEnum::PATROL_SHIP);
        $patrollerConfig->setMechanics([new PatrolShip()]);

        $patroller = new GameEquipment($roomStart);
        $patroller->setName(EquipmentEnum::PATROL_SHIP);
        $patroller->setEquipment($patrollerConfig);

        $this->playerService->shouldReceive('persist');

        $player = $this->createPlayer($daedalus, $roomStart);

        $this->action->loadParameters($this->actionEntity, $player, $patroller);

        $this->placeService->shouldReceive('findByNameAndDaedalus')->andReturn($roomEnd);
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->randomService->shouldReceive('randomPercent')->andReturn(0);
        $this->eventService->shouldReceive('callEvent')->times(1);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEquals($player->getPlace(), $roomEnd);
    }
}
