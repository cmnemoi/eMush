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
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\FakeGetRandomElementsFromArrayService;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Factory\PlayerFactory;
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
    protected function setUp(): void
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
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testShouldHandleFireEventsPutFireInNotBurningRoom(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // when we handle fire events
        $this->service->handleFireEvents($daedalus->getRooms()->toArray(), new \DateTime());

        // then we should have one fire event
        $this->statusService
            ->shouldHaveReceived('createStatusFromName')
            ->once();
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
        $this->service->handleTremorEvents($daedalus->getRooms()->toArray(), new \DateTime());

        // then we should have one tremor event
        $this->eventService
            ->shouldHaveReceived('callEvent')
            ->once();
    }

    public function testShouldHandleElectricArcEvents()
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given laboratory
        $laboratory = $daedalus->getPlaceByName(RoomEnum::LABORATORY);

        // when we handle electric arc events
        $this->service->handleElectricArcEvents($daedalus->getRooms()->toArray(), new \DateTime());

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
        $this->randomService->shouldReceive('getRandomDaedalusEquipmentFromProbaCollection')->once()->andReturn([$mycoscan]);

        // when we handle equipment break events
        $this->service->handleEquipmentBreak(new ProbaCollection([$mycoscan->getName() => 1]), $daedalus, new \DateTime());

        // then we should have one equipment break event
        $this->statusService
            ->shouldHaveReceived('createStatusFromName')
            ->once();
    }

    public function testShouldHandleDoorBreakWithBreakableDoor(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        $medlab = Place::createRoomByNameInDaedalus(RoomEnum::MEDLAB, $daedalus);
        $laboratory = $daedalus->getPlaceByName(RoomEnum::LABORATORY);

        // given a door
        $door = Door::createFromRooms($medlab, $laboratory);

        // when we handle door break events
        $this->service->handleDoorBreak([$door], new \DateTime());

        // then we should have one door break event
        $this->statusService
            ->shouldHaveReceived('createStatusFromName')
            ->once();
    }

    public function testShouldHandlePanicCrisisWithHumanPlayer(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given a player in this Daedalus
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);

        // when we handle panic crisis events
        $this->service->handlePanicCrisis($daedalus->getAlivePlayers()->toArray(), new \DateTime());

        // then we should have one panic event
        $this->eventService
            ->shouldHaveReceived('callEvent')
            ->once();
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
        $this->service->handleMetalPlates($daedalus->getAlivePlayers()->toArray(), new \DateTime());

        // then we should have one metal plates event
        $this->eventService
            ->shouldHaveReceived('callEvent')
            ->once();
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

        // when we handle break oxygen tank events
        $this->service->handleOxygenTankBreak([$oxygenTank], new \DateTime());

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

        // when we handle break fuel tank events
        $this->service->handleFuelTankBreak([$fuelTank], new \DateTime());

        // then we should have one break event
        $this->statusService
            ->shouldHaveReceived('createStatusFromName')
            ->once();
    }
}
