<?php

namespace Mush\Tests\unit\Daedalus\Service;

use Mockery;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Daedalus\Service\DaedalusIncidentService;
use Mush\Daedalus\Service\DaedalusIncidentServiceInterface;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\Repository\GameEquipmentRepositoryInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\FakeGetRandomElementsFromArrayService;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DaedalusIncidentServiceTest extends TestCase
{
    /** @var Mockery\Mock|RandomServiceInterface */
    private RandomServiceInterface $randomService;

    /** @var EventServiceInterface|Mockery\Spy */
    private EventServiceInterface $eventService;

    /** @var GameEquipmentRepositoryInterface|Mockery\Mock */
    private GameEquipmentRepositoryInterface $gameEquipmentRepository;

    /** @var Mockery\Spy|StatusServiceInterface */
    private StatusServiceInterface $statusService;

    private DaedalusIncidentServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->eventService = \Mockery::spy(EventServiceInterface::class);
        $this->gameEquipmentRepository = \Mockery::mock(GameEquipmentRepositoryInterface::class);
        $this->statusService = \Mockery::spy(StatusServiceInterface::class);

        $this->service = new DaedalusIncidentService(
            eventService: $this->eventService,
            gameEquipmentRepository: $this->gameEquipmentRepository,
            getRandomElementsFromArray: new FakeGetRandomElementsFromArrayService(),
            randomService: $this->randomService,
            statusService: $this->statusService,
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

        // when we handle fire events
        $this->service->handleFireEvents($daedalus, new \DateTime());

        // then we should have one fire event
        $this->statusService
            ->shouldHaveReceived('createStatusFromName')
            ->once();
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
        $this->service->handleFireEvents($daedalus, new \DateTime());

        // then we should have no fire event
        $this->statusService->shouldNotHaveReceived('createStatusFromName');
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

        // when we handle tremor events
        $this->service->handleTremorEvents($daedalus, new \DateTime());

        // then we should have one tremor event
        $this->eventService
            ->shouldHaveReceived('callEvent')
            ->once();
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
        $this->service->handleTremorEvents($daedalus, new \DateTime());

        // then we should have 0 tremor event
        $this->eventService->shouldNotHaveReceived('callEvent');
    }

    public function testShouldNotHandleTremorEventsInRoomWithoutPlayers()
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given a room in this Daedalus
        $room = Place::createRoomByNameInDaedalus(RoomEnum::LABORATORY, $daedalus);

        // when we handle tremor events
        $this->service->handleTremorEvents($daedalus, new \DateTime());

        // then we should have 0 tremor event
        $this->eventService->shouldNotHaveReceived('callEvent');
    }

    public function testShouldHandleElectricArcEvents()
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given laboratory
        $laboratory = $daedalus->getPlaceByName(RoomEnum::LABORATORY);

        // when we handle electric arc events
        $this->service->handleElectricArcEvents($daedalus, new \DateTime());

        // then we should have one electric arc event
        $this->eventService->shouldHaveReceived('callEvent')->once();
    }

    public function testShouldHandleEquipmentBreakWithEquipmentToBreak(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();
        $difficultyConfig = $daedalus->getGameConfig()->getDifficultyConfig();
        $difficultyConfig->setEquipmentBreakRateDistribution([EquipmentEnum::MYCOSCAN => 1]);

        $lab = $daedalus->getPlaceByName(RoomEnum::LABORATORY);
        $mycoscan = $lab->getEquipmentByName(EquipmentEnum::MYCOSCAN);

        // setup universe state
        $this->gameEquipmentRepository->shouldReceive('findByNameAndDaedalus')->once()->andReturn([$mycoscan]);
        $this->randomService->shouldReceive('getRandomDaedalusEquipmentFromProbaCollection')->once()->andReturn([$mycoscan]);

        // when we handle equipment break events
        $this->service->handleEquipmentBreak($daedalus, new \DateTime());

        // then we should have one equipment break event
        $this->statusService
            ->shouldHaveReceived('createStatusFromName')
            ->once();
    }

    public function testShouldNotHandleEquipementBreakWithEquipmentAlreadyBroken(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();
        $difficultyConfig = $daedalus->getGameConfig()->getDifficultyConfig();
        $difficultyConfig->setEquipmentBreakRateDistribution([EquipmentEnum::MYCOSCAN => 1]);

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
        $this->service->handleEquipmentBreak($daedalus, new \DateTime());

        // then we should have no equipment break event
        $this->statusService->shouldNotHaveReceived('createStatusFromName');
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

        // when we handle door break events
        $this->service->handleDoorBreak($daedalus, new \DateTime());

        // then we should have one door break event
        $this->statusService
            ->shouldHaveReceived('createStatusFromName')
            ->once();
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

        // when we handle door break events
        $this->service->handleDoorBreak($daedalus, new \DateTime());

        // then we should have one door break event
        $this->statusService->shouldNotHaveReceived('createStatusFromName');
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

        // when we handle door break events
        $this->service->handleDoorBreak($daedalus, new \DateTime());

        // then we should not have any door break event
        $this->statusService->shouldNotHaveReceived('createStatusFromName');
    }

    public function testShouldHandlePanicCrisisWithHumanPlayer(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given a player in this Daedalus
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);

        // when we handle panic crisis events
        $this->service->handlePanicCrisis($daedalus, new \DateTime());

        // then we should have one panic event
        $this->eventService
            ->shouldHaveReceived('callEvent')
            ->once();
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
        $this->service->handlePanicCrisis($daedalus, new \DateTime());

        // then we should not have any panic event
        $this->eventService->shouldNotHaveReceived('callEvent');
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

        // when we handle metal plates events
        $this->service->handleMetalPlates($daedalus, new \DateTime());

        // then we should have one metal plates event
        $this->eventService
            ->shouldHaveReceived('callEvent')
            ->once();
    }

    public function testShouldNotHandleMetalPlatesWithNoPlayersInRoom(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given a room in this Daedalus
        $room = Place::createRoomByNameInDaedalus(RoomEnum::LABORATORY, $daedalus);

        // when we handle metal plates events
        $this->service->handleMetalPlates($daedalus, new \DateTime());

        // then we should not have any metal plates event
        $this->eventService->shouldNotHaveReceived('callEvent');
    }

    public function testShouldNotHandleMetalPlatesIfPlayerOnAPlanet(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given a planet in this Daedalus
        $planet = Place::createPlanetPlaceForDaedalus(RoomEnum::PLANET, $daedalus);

        // given a player in this planet
        PlayerFactory::createPlayerInPlace($planet);

        // when we handle metal plates events
        $this->service->handleMetalPlates($daedalus, new \DateTime());

        // then we should not have any metal plates event
        $this->eventService->shouldNotHaveReceived('callEvent');
    }

    public function testShouldNotHandleMetalPlatesIfPlayerIsInSpace(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given a player in space
        $player = PlayerFactory::createPlayerInPlace($daedalus->getPlaceByName(RoomEnum::SPACE));

        // when we handle metal plates events
        $this->service->handleMetalPlates($daedalus, new \DateTime());

        // then we should not have any metal plates event
        $this->eventService->shouldNotHaveReceived('callEvent');
    }

    public function testShouldNotHandleMetalPlatesIfPlayerIsInPatrolShip(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given a player in patrol ship
        $player = PlayerFactory::createPlayerInPlace(Place::createPatrolShipPlaceForDaedalus(RoomEnum::PATROL_SHIP_ALPHA_JUJUBE, $daedalus));

        // when we handle metal plates events
        $this->service->handleMetalPlates($daedalus, new \DateTime());

        // then we should not have any metal plates event
        $this->eventService->shouldNotHaveReceived('callEvent');
    }

    public function testShouldHandleOxygenTankBreak(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given an oxygen tank
        $oxygenTank = GameEquipmentFactory::createEquipmentByNameForHolder(
            EquipmentEnum::OXYGEN_TANK,
            $daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY)
        );

        // setup universe state
        $this->gameEquipmentRepository->shouldReceive('findByNameAndDaedalus')->once()->andReturn([$oxygenTank]);

        // when we handle break oxygen tank events
        $this->service->handleOxygenTankBreak($daedalus, new \DateTime());

        // then we should have one break event
        $this->statusService
            ->shouldHaveReceived('createStatusFromName')
            ->once();
    }

    public function testShouldHandleFuelTankBreak(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given a fuel tank
        $fuelTank = GameEquipmentFactory::createEquipmentByNameForHolder(
            EquipmentEnum::FUEL_TANK,
            $daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY)
        );

        // setup universe state
        $this->gameEquipmentRepository->shouldReceive('findByNameAndDaedalus')->once()->andReturn([$fuelTank]);

        // when we handle break fuel tank events
        $this->service->handleFuelTankBreak($daedalus, new \DateTime());

        // then we should have one break event
        $this->statusService
            ->shouldHaveReceived('createStatusFromName')
            ->once();
    }
}
