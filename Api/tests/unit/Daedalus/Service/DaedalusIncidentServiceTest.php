<?php

namespace unit\Daedalus\Service;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusIncidentService;
use Mush\Daedalus\Service\DaedalusIncidentServiceInterface;
use Mush\Equipment\Criteria\GameEquipmentCriteria;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEventEnum;
use Mush\Place\Event\RoomEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DaedalusIncidentServiceTest extends TestCase
{
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var EventDispatcherInterface | Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;
    /** @var GameEquipmentRepository | Mockery\Mock */
    private GameEquipmentRepository $gameEquipmentRepository;

    private DaedalusIncidentServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->gameEquipmentRepository = Mockery::mock(GameEquipmentRepository::class);

        $this->service = new DaedalusIncidentService(
            $this->randomService,
            $this->eventDispatcher,
            $this->gameEquipmentRepository
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testHandleFireEvents()
    {
        $this->randomService->shouldReceive('random')->andReturn(0)->once();
        $this->randomService->shouldReceive('getRandomElements')->andReturn([])->once();

        $fires = $this->service->handleFireEvents(new Daedalus(), new \DateTime());

        $this->assertEquals(0, $fires);

        $this->randomService->shouldReceive('random')->andReturn(1)->once();

        $room1 = new Place();

        $this->randomService
            ->shouldReceive('getRandomElements')
            ->andReturn([$room1])
            ->once()
        ;

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (RoomEvent $event) => $event->getRoom() === $room1 && $event->getReason() === RoomEventEnum::CYCLE_FIRE)
            ->once()
        ;

        $fires = $this->service->handleFireEvents(new Daedalus(), new \DateTime());

        $this->assertEquals(1, $fires);
    }

    public function testHandleTremorEvents()
    {
        $this->randomService->shouldReceive('random')->andReturn(0)->once();
        $this->randomService->shouldReceive('getRandomElements')->andReturn([])->once();

        $fires = $this->service->handleTremorEvents(new Daedalus(), new \DateTime());

        $this->assertEquals(0, $fires);

        $this->randomService->shouldReceive('random')->andReturn(1)->once();

        $room1 = new Place();

        $this->randomService
            ->shouldReceive('getRandomElements')
            ->andReturn([$room1])
            ->once()
        ;

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (RoomEvent $event) => $event->getRoom() === $room1 && $event->getReason() === RoomEventEnum::TREMOR)
            ->once()
        ;

        $fires = $this->service->handleTremorEvents(new Daedalus(), new \DateTime());

        $this->assertEquals(1, $fires);
    }

    public function testHandleElectricArcEvents()
    {
        $this->randomService->shouldReceive('random')->andReturn(0)->once();
        $this->randomService->shouldReceive('getRandomElements')->andReturn([])->once();

        $fires = $this->service->handleElectricArcEvents(new Daedalus(), new \DateTime());

        $this->assertEquals(0, $fires);

        $this->randomService->shouldReceive('random')->andReturn(1)->once();

        $room1 = new Place();

        $this->randomService
            ->shouldReceive('getRandomElements')
            ->andReturn([$room1])
            ->once()
        ;

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (RoomEvent $event) => $event->getRoom() === $room1 && $event->getReason() === RoomEventEnum::ELECTRIC_ARC)
            ->once()
        ;

        $fires = $this->service->handleElectricArcEvents(new Daedalus(), new \DateTime());

        $this->assertEquals(1, $fires);
    }

    public function testHandleEquipmentBreakEvents()
    {
        $this->randomService->shouldReceive('random')->andReturn(0)->once();

        $broken = $this->service->handleEquipmentBreak(new Daedalus(), new \DateTime());

        $this->assertEquals(0, $broken);

        $this->randomService->shouldReceive('random')->andReturn(1)->once();

        $equipment = new GameEquipment();

        $this->gameEquipmentRepository
            ->shouldReceive('findByCriteria')
            ->withArgs(fn (GameEquipmentCriteria $criteria) => $criteria->getNotInstanceOf() === [Door::class])
            ->andReturn([$equipment])
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getRandomElements')
            ->andReturn([$equipment])
            ->once()
        ;

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (EquipmentEvent $event) => $event->getEquipment() === $equipment)
            ->once()
        ;

        $broken = $this->service->handleEquipmentBreak(new Daedalus(), new \DateTime());

        $this->assertEquals(1, $broken);
    }

    public function testHandleDoorBreakEvents()
    {
        $this->randomService->shouldReceive('random')->andReturn(0)->once();

        $broken = $this->service->handleDoorBreak(new Daedalus(), new \DateTime());

        $this->assertEquals(0, $broken);

        $this->randomService->shouldReceive('random')->andReturn(1)->once();

        $door = new Door();

        $this->gameEquipmentRepository
            ->shouldReceive('findByCriteria')
            ->withArgs(fn (GameEquipmentCriteria $criteria) => $criteria->getInstanceOf() === [Door::class])
            ->andReturn([$door])
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getRandomElements')
            ->andReturn([$door])
            ->once()
        ;

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (EquipmentEvent $event) => $event->getEquipment() === $door)
            ->once()
        ;

        $broken = $this->service->handleDoorBreak(new Daedalus(), new \DateTime());

        $this->assertEquals(1, $broken);
    }
}
