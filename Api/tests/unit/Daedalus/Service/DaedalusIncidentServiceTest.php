<?php

namespace Mush\Tests\unit\Daedalus\Service;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Daedalus\Service\DaedalusIncidentService;
use Mush\Daedalus\Service\DaedalusIncidentServiceInterface;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\FakeGetRandomElementsFromArrayService;
use Mush\Game\Service\Random\FakeGetRandomPoissonIntegerService;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Place\Event\RoomEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
final class DaedalusIncidentServiceTest extends TestCase
{
    /** @var Mockery\Mock|RandomServiceInterface */
    private RandomServiceInterface $randomService;

    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    /** @var GameEquipmentRepository|Mockery\Mock */
    private GameEquipmentRepository $gameEquipmentRepository;

    /** @var LoggerInterface|Mockery\Mock */
    private LoggerInterface $logger;

    /** @var Mockery\Mock|StatusServiceInterface */
    private StatusServiceInterface $statusService;

    private DaedalusIncidentServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->gameEquipmentRepository = \Mockery::mock(GameEquipmentRepository::class);
        $this->logger = \Mockery::mock(LoggerInterface::class);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->service = new DaedalusIncidentService(
            new FakeGetRandomElementsFromArrayService(),
            new FakeGetRandomPoissonIntegerService(1), // always one incident
            $this->randomService,
            $this->eventService,
            $this->gameEquipmentRepository,
            $this->statusService,
            $this->logger,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testShouldHandleFireEventsPutFireInNotBurningRoom(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // setup universe state
        $this->statusService->shouldReceive('createStatusFromName')->once();

        // when we handle fire events
        $fires = $this->service->handleFireEvents($daedalus, new \DateTime());

        // then we should have one fire event
        self::assertSame(1, $fires);
    }

    public function testShouldNotHandleFireEventsInBurningRoom(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given laboratory is burning
        StatusFactory::createStatusByNameForHolder(
            name: StatusEnum::FIRE,
            holder: $daedalus->getPlaceByName(RoomEnum::LABORATORY),
        );

        // when we handle fire events
        $fires = $this->service->handleFireEvents($daedalus, new \DateTime());

        // then we should have no fire event
        self::assertSame(0, $fires);
    }

    public function testShouldHandleTremorEventsInRoomWithAlivePlayers()
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given a room in this Daedalus
        $room = Place::createRoomByNameInDaedalus(RoomEnum::LABORATORY, $daedalus);

        // given a player in this room
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $player->changePlace($room);

        // setup universe state
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(static fn (RoomEvent $event) => $event->getPlace() === $room && \in_array(EventEnum::NEW_CYCLE, $event->getTags(), true))
            ->once();

        // when we handle tremor events
        $tremorEvents = $this->service->handleTremorEvents($daedalus, new \DateTime());

        // then we should have one tremor event
        self::assertSame(1, $tremorEvents);
    }

    public function testShouldNotHandleTremorEventsInRoomWithDeadPlayers()
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given a room in this Daedalus
        $room = Place::createRoomByNameInDaedalus(RoomEnum::LABORATORY, $daedalus);

        // given a player in this room
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $player->changePlace($room);

        // given player is dead
        $player->kill();

        // when we handle tremor events
        $tremorEvents = $this->service->handleTremorEvents($daedalus, new \DateTime());

        // then we should have 0 tremor event
        self::assertSame(0, $tremorEvents);
    }

    public function testShouldNotHandleTremorEventsInRoomWithoutPlayers()
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given a room in this Daedalus
        Place::createRoomByNameInDaedalus(RoomEnum::LABORATORY, $daedalus);

        // when we handle tremor events
        $tremorEvents = $this->service->handleTremorEvents($daedalus, new \DateTime());

        // then we should have 0 tremor event
        self::assertSame(0, $tremorEvents);
    }

    public function testShouldHandleElectricArcEvents()
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given laboratory
        $laboratory = $daedalus->getPlaceByName(RoomEnum::LABORATORY);

        // setup universe state
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(static fn (RoomEvent $event) => $event->getPlace() === $laboratory && \in_array(EventEnum::NEW_CYCLE, $event->getTags(), true))
            ->once();

        // when we handle electric arc events
        $electricArcs = $this->service->handleElectricArcEvents($daedalus, new \DateTime());

        // then we should have one fire event
        self::assertSame(1, $electricArcs);
    }

    public function testShouldHandleEquipmentBreakWithEquipmentToBreak(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        $lab = $daedalus->getPlaceByName(RoomEnum::LABORATORY);
        $mycoscan = $lab->getEquipmentByName(EquipmentEnum::MYCOSCAN);

        // setup universe state
        $this->gameEquipmentRepository->shouldReceive('findByNameAndDaedalus')->once()->andReturn([$mycoscan]);
        $this->randomService->shouldReceive('getRandomDaedalusEquipmentFromProbaCollection')->once()->andReturn([$mycoscan]);
        $this->statusService->shouldReceive('createStatusFromName')->once();

        // when we handle equipment break events
        $equipmentBreaks = $this->service->handleEquipmentBreak($daedalus, new \DateTime());

        // then we should have one equipment break event
        self::assertSame(1, $equipmentBreaks);

    }

    public function testShouldNotHandleEquipementBreakWithEquipmentAlreadyBroken(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        $lab = $daedalus->getPlaceByName(RoomEnum::LABORATORY);
        $mycoscan = $lab->getEquipmentByName(EquipmentEnum::MYCOSCAN);

        // given this equipment is broken
        StatusFactory::createStatusByNameForHolder(
            name: EquipmentStatusEnum::BROKEN,
            holder: $mycoscan,
        );

        // setup universe state
        $this->gameEquipmentRepository->shouldReceive('findByNameAndDaedalus')->once()->andReturn([$mycoscan]);

        // when we handle equipment break events
        $equipmentBreaks = $this->service->handleEquipmentBreak($daedalus, new \DateTime());

        // then we should have no equipment break event
        self::assertSame(0, $equipmentBreaks);
    }

    public function testShouldHandleDoorBreakWithBreakableDoor(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        $medlab = Place::createRoomByNameInDaedalus(RoomEnum::MEDLAB, $daedalus);
        $laboratory = $daedalus->getPlaceByName(RoomEnum::LABORATORY);

        // given a door
        $door = Door::createFromRooms($medlab, $laboratory);

        // setup universe state
        $this->gameEquipmentRepository->shouldReceive('findByCriteria')->once()->andReturn([$door]);
        $this->statusService->shouldReceive('createStatusFromName')->once();

        // when we handle door break events
        $doorBreaks = $this->service->handleDoorBreak($daedalus, new \DateTime());

        // then we should have one door break event
        self::assertSame(1, $doorBreaks);
    }

    public function testShouldNotHandleDoorBreakWithNotBreakableDoor(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        $medlab = Place::createRoomByNameInDaedalus(RoomEnum::MEDLAB, $daedalus);
        $laboratory = $daedalus->getPlaceByName(RoomEnum::LABORATORY);

        // given a door
        $door = Door::createFromRooms($medlab, $laboratory);

        // given this door is broken
        StatusFactory::createStatusByNameForHolder(
            name: EquipmentStatusEnum::BROKEN,
            holder: $door
        );

        // setup universe state
        $this->gameEquipmentRepository->shouldReceive('findByCriteria')->once()->andReturn([$door]);
        $this->statusService->shouldReceive('createStatusFromName')->never();

        // when we handle door break events
        $doorBreaks = $this->service->handleDoorBreak($daedalus, new \DateTime());

        // then we should have one door break event
        self::assertSame(0, $doorBreaks);
    }

    public function testShouldNotHandleDoorBreakIfBreakableDoorIsAlreadyBroken(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        $frontCorridor = Place::createRoomByNameInDaedalus(RoomEnum::FRONT_CORRIDOR, $daedalus);
        $laboratory = $daedalus->getPlaceByName(RoomEnum::LABORATORY);

        // given a door
        $door = Door::createFromRooms($frontCorridor, $laboratory);

        // setup universe state
        $this->gameEquipmentRepository->shouldReceive('findByCriteria')->once()->andReturn([$door]);
        $this->statusService->shouldReceive('createStatusFromName')->never();

        // when we handle door break events
        $doorBreaks = $this->service->handleDoorBreak($daedalus, new \DateTime());

        // then we should have one door break event
        self::assertSame(0, $doorBreaks);
    }

    public function testShouldHandlePanicCrisisWithHumanPlayer(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given a player in this Daedalus
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);

        // setup universe state
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(static fn (PlayerEvent $event) => $event->getPlayer() === $player)
            ->once();

        // when we handle panic crisis events
        $panics = $this->service->handlePanicCrisis($daedalus, new \DateTime());

        // then we should have one panic event
        self::assertSame(1, $panics);
    }

    public function testShouldNotHandlePanicCrisisWithMushPlayer(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given a player in this Daedalus
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);

        // given this player is Mush
        StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::MUSH,
            holder: $player,
        );

        // when we handle panic crisis events
        $panics = $this->service->handlePanicCrisis($daedalus, new \DateTime());

        // then we should not have any panic event
        self::assertSame(0, $panics);
    }

    public function testShouldHandleMetalPlatesWithPlayersInRoom(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given a room in this Daedalus
        $room = Place::createRoomByNameInDaedalus(RoomEnum::LABORATORY, $daedalus);

        // given a player in this room
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $player->changePlace($room);

        // setup universe state
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(static fn (PlayerEvent $event) => $event->getPlace() === $room && \in_array(EventEnum::NEW_CYCLE, $event->getTags(), true))
            ->once();

        // when we handle metal plates events
        $metalPlates = $this->service->handleMetalPlates($daedalus, new \DateTime());

        // then we should have one metal plates event
        self::assertSame(1, $metalPlates);
    }

    public function testShouldNotHandleMetalPlatesWithNoPlayersInRoom(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given a room in this Daedalus
        Place::createRoomByNameInDaedalus(RoomEnum::LABORATORY, $daedalus);

        // when we handle metal plates events
        $metalPlates = $this->service->handleMetalPlates($daedalus, new \DateTime());

        // then we should not have any metal plates event
        self::assertSame(0, $metalPlates);
    }
}
