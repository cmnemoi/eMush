<?php

declare(strict_types=1);

namespace Mush\Daedalus\tests\unit\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\CycleIncidentEnum;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Daedalus\Service\DaedalusIncidentService;
use Mush\Daedalus\Service\DispatchCycleIncidentsService;
use Mush\Equipment\ConfigData\EquipmentConfigData;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\Repository\InMemoryGameEquipmentRepository;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\FakeD100RollService;
use Mush\Game\Service\Random\FakeGetRandomElementsFromArrayService;
use Mush\Game\Service\Random\FakeProbaCollectionRandomElementService;
use Mush\Game\Service\Random\FakeRandomFloatService;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\DoorEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Place\Event\RoomEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Factory\PlayerFactory;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Factory\ProjectFactory;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Status\Service\FakeStatusService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DispatchCycleIncidentsServiceTest extends TestCase
{
    private DispatchCycleIncidentsService $dispatchCycleIncidents;

    private DaedalusIncidentService $daedalusIncidentService;
    private FakeD100RollService $d100Roll;
    private EventServiceInterface $eventService;
    private FakeProbaCollectionRandomElementService $probaCollectionRandomElement;
    private FakeRandomFloatService $randomFloat;

    private InMemoryGameEquipmentRepository $gameEquipmentRepository;
    private RandomServiceInterface $randomService;
    private FakeStatusService $statusService;
    private FakeGetRandomElementsFromArrayService $getRandomElementsFromArray;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->gameEquipmentRepository = new InMemoryGameEquipmentRepository();
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->statusService = new FakeStatusService();
        $this->getRandomElementsFromArray = new FakeGetRandomElementsFromArrayService();
        $this->eventService = \Mockery::spy(EventServiceInterface::class);

        $this->daedalusIncidentService = new DaedalusIncidentService(
            eventService: $this->eventService,
            gameEquipmentRepository: $this->gameEquipmentRepository,
            getRandomElementsFromArray: $this->getRandomElementsFromArray,
            randomService: $this->randomService,
            statusService: $this->statusService,
        );
        $this->d100Roll = new FakeD100RollService();
        $this->probaCollectionRandomElement = new FakeProbaCollectionRandomElementService();
        $this->randomFloat = new FakeRandomFloatService();

        $this->dispatchCycleIncidents = new DispatchCycleIncidentsService(
            daedalusIncidentService: $this->daedalusIncidentService,
            d100Roll: $this->d100Roll,
            eventService: $this->eventService,
            gameEquipmentRepository: $this->gameEquipmentRepository,
            probaCollectionRandomElement: $this->probaCollectionRandomElement,
            randomFloat: $this->randomFloat,
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testShouldNotBeExecutedIfDaedalusIsFilling(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::STARTING);

        // When
        $result = $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->thenIncidentsShouldNotBeExecuted($result, 'Incidents should not be executed if Daedalus is filling');
    }

    public function testShouldNotBeExecutedIfPreventedByBricBroc(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectIsFinished($daedalus);
        $this->givenD100RollIsSuccessful();

        // When
        $result = $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->thenIncidentsShouldNotBeExecuted($result, 'BricBroc should prevent the execution');
    }

    public function testShouldNotBeExecutedIfNotEnoughIncidentPoints(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenRandomFloatIsVerySmall();

        // When
        $result = $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->thenIncidentsShouldNotBeExecuted($result, 'Not enough incident points should prevent the execution');
    }

    public function testShouldMakeRoomBurnIfSelected(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 4);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::FIRE);
        $room = $this->givenLaboratoryExists($daedalus);

        // When
        $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->thenRoomShouldBeBurning($room);
    }

    public function testShouldNotDispatchFireIncidentIfAllRoomsAreBurning(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 4);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::FIRE);
        $room = $this->givenLaboratoryExists($daedalus);
        StatusFactory::createStatusByNameForHolder(
            name: StatusEnum::FIRE,
            holder: $room,
        );

        // When
        $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->eventService->shouldNotHaveReceived('callEvent');
    }

    public function testShouldBreakOxygenTankIfSelected(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 3);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::OXYGEN_LEAK);
        $oxygenTank = $this->givenEquipmentInLaboratory($daedalus, EquipmentEnum::OXYGEN_TANK);

        // When
        $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->thenEquipmentShouldBeBroken($oxygenTank, 'Oxygen tank should be broken');
    }

    public function testShouldNotBreakOxygenTankIfAllOxygenTanksAreBroken(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 3);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::OXYGEN_LEAK);
        $oxygenTank = $this->givenEquipmentInLaboratory($daedalus, EquipmentEnum::OXYGEN_TANK);
        StatusFactory::createStatusByNameForHolder(
            name: EquipmentStatusEnum::BROKEN,
            holder: $oxygenTank,
        );

        // When
        $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->eventService->shouldNotHaveReceived('callEvent');
    }

    public function testShouldBreakFuelTankIfSelected(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 3);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::FUEL_LEAK);
        $fuelTank = $this->givenEquipmentInLaboratory($daedalus, EquipmentEnum::FUEL_TANK);

        // When
        $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->thenEquipmentShouldBeBroken($fuelTank, 'Fuel tank should be broken');
    }

    public function testShouldNotBreakFuelTankIfAllFuelTanksAreBroken(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 3);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::FUEL_LEAK);
        $fuelTank = $this->givenEquipmentInLaboratory($daedalus, EquipmentEnum::FUEL_TANK);
        StatusFactory::createStatusByNameForHolder(
            name: EquipmentStatusEnum::BROKEN,
            holder: $fuelTank,
        );

        // When
        $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->eventService->shouldNotHaveReceived('callEvent');
    }

    public function testShouldBreakDoorIfSelected(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 3);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::DOOR_BLOCKED);
        $door = $this->givenDoorBetweenLaboratoryAndMedlab($daedalus);

        // When
        $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->thenEquipmentShouldBeBroken($door, 'Door should be broken');
    }

    public function testShouldNotBreakDoorIfNotBreakable(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 3);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::DOOR_BLOCKED);
        $door = $this->givenDoorBetweenLaboratoryAndFrontCorridor($daedalus);

        // When
        $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->eventService->shouldNotHaveReceived('callEvent');
    }

    public function testShouldNotBreakDoorIfAllDoorsAreBroken(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 3);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::DOOR_BLOCKED);
        $door = $this->givenDoorBetweenLaboratoryAndFrontCorridor($daedalus);
        StatusFactory::createStatusByNameForHolder(
            name: EquipmentStatusEnum::BROKEN,
            holder: $door,
        );

        // When
        $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->eventService->shouldNotHaveReceived('callEvent');
    }

    public function testShouldBreakEquipmentIfSelected(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 3);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::EQUIPMENT_FAILURE);
        $equipment = $this->givenEquipmentInLaboratory($daedalus, EquipmentEnum::GRAVITY_SIMULATOR);
        $this->givenRandomServiceWillReturn([$equipment]);

        // When
        $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->thenEquipmentShouldBeBroken($equipment, 'Equipment should be broken');
    }

    public function testShouldNotBreakEquipmentIfAllEquipmentAreBroken(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 3);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::EQUIPMENT_FAILURE);
        $equipment = $this->givenEquipmentInLaboratory($daedalus, EquipmentEnum::GRAVITY_SIMULATOR);
        StatusFactory::createStatusByNameForHolder(
            name: EquipmentStatusEnum::BROKEN,
            holder: $equipment,
        );

        // When
        $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->eventService->shouldNotHaveReceived('callEvent');
    }

    public function testShouldSteelPlatePlayerIfSelected(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 2);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::ACCIDENT);
        $player = $this->givenPlayerInDaedalus($daedalus);

        // When
        $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->thenEventShouldBeCalledWithTag(PlayerEvent::METAL_PLATE);
    }

    public function testShouldCreatePanicCrisisIfSelected(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 2);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::ANXIETY_ATTACK);
        $player = $this->givenPlayerInDaedalus($daedalus);

        // When
        $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->thenEventShouldBeCalledWithTag(PlayerEvent::PANIC_CRISIS);
    }

    public function testShouldMakePlayerSickIfSelected(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 3);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::BOARD_DISEASE);
        $player = $this->givenPlayerInDaedalus($daedalus);

        // When
        $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->thenEventShouldBeCalledWithTag(PlayerEvent::CYCLE_DISEASE);
    }

    public function testShouldCreateTremorIfSelected(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 2);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::JOLT);
        $player = $this->givenPlayerInDaedalus($daedalus);

        // When
        $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->thenEventShouldBeCalledWithTag(RoomEvent::TREMOR);
    }

    public function testShouldCreateElectricArcIfSelected(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 10);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::ELECTROCUTION);

        // When
        $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->thenEventShouldBeCalledWithTag(RoomEvent::ELECTRIC_ARC);
    }

    public function testShouldNotSelectPlayerForAccidentTwice(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 4);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::ACCIDENT);
        $this->givenPlayerInDaedalus($daedalus);

        // Then
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Incident accident not found');

        // When
        $this->whenDispatchingCycleIncidents($daedalus);
    }

    public function testShouldNotSelectPlayerForJoltTwice(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 4);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::JOLT);
        $this->givenPlayerInDaedalus($daedalus);

        // Then
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Incident jolt not found');

        // When
        $this->whenDispatchingCycleIncidents($daedalus);
    }

    public function testShouldNotSelectPlayerForBoardDiseaseTwice(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 6);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::BOARD_DISEASE);
        $this->givenPlayerInDaedalus($daedalus);

        // Then
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Incident board_disease not found');

        // When
        $this->whenDispatchingCycleIncidents($daedalus);
    }

    public function testShouldNotSelectPlayerForElectrocutionTwice(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 16);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::ELECTROCUTION);
        $this->givenPlayerInDaedalus($daedalus);

        // Then
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Incident electrocution not found');

        // When
        $this->whenDispatchingCycleIncidents($daedalus);
    }

    public function testShouldNotSelectDeadPlayerForAccident(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 4);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::ACCIDENT);
        $player = $this->givenPlayerInDaedalus($daedalus);
        $player->kill();

        // Then
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Incident accident not found');

        // When
        $this->whenDispatchingCycleIncidents($daedalus);
    }

    public function testShouldNotSelectDeadPlayerForJolt(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 4);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::JOLT);
        $player = $this->givenPlayerInDaedalus($daedalus);
        $player->kill();

        // Then
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Incident jolt not found');

        // When
        $this->whenDispatchingCycleIncidents($daedalus);
    }

    public function testShouldNotSelectDeadPlayerForBoardDisease(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 3);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::BOARD_DISEASE);
        $player = $this->givenPlayerInDaedalus($daedalus);
        $player->kill();

        // When
        $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->eventService->shouldNotHaveReceived('callEvent');
    }

    public function testShouldNotSelectDeadPlayerForAnxietyAttack(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 2);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::ANXIETY_ATTACK);
        $player = $this->givenPlayerInDaedalus($daedalus);
        $player->kill();

        // When
        $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->eventService->shouldNotHaveReceived('callEvent');
    }

    public function testShouldNotSelectMushPlayerForBoardDisease(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 3);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::BOARD_DISEASE);
        $player = $this->givenPlayerInDaedalus($daedalus);
        StatusFactory::createChargeStatusWithName(
            PlayerStatusEnum::MUSH,
            $player,
        );

        // Then
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Incident board_disease not found');

        // When
        $this->whenDispatchingCycleIncidents($daedalus);
    }

    public function shouldNotSelectPlayerInSpaceForAccident(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 4);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::ACCIDENT);
        $player = $this->givenPlayerInDaedalus($daedalus);
        $player->changePlace($daedalus->getPlaceByName(RoomEnum::SPACE));

        // When
        $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->eventService->shouldNotHaveReceived('callEvent');
    }

    public function testShouldNotSelectMushPlayerForAnxietyAttack(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 2);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::ANXIETY_ATTACK);
        $player = $this->givenPlayerInDaedalus($daedalus);
        StatusFactory::createChargeStatusWithName(
            PlayerStatusEnum::MUSH,
            $player,
        );

        // Then
        // We except an exception because other incidents support Mush player targets
        // but we force the incident anxiety_attack to be selected
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Incident anxiety_attack not found');

        // When
        $this->whenDispatchingCycleIncidents($daedalus);
    }

    public function testShouldNotSelectSamePlayerForAnxietyAttackTwice(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 4);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::ANXIETY_ATTACK);
        $this->givenPlayerInDaedalus($daedalus);

        // Then
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Incident anxiety_attack not found');

        // When
        $this->whenDispatchingCycleIncidents($daedalus);
    }

    public function testShouldCallRepositoryOnlyOnceForIncidentTargetSelectionAndDispatch(): void
    {
        // Given
        $repository = $this->givenRepositoryThatCountsFindByNameAndDaedalusCalls();
        $daedalus = $this->givenADaedalusWithSingleEquipmentTypeInDistribution();
        $this->givenDaedalusIsInState($daedalus, GameStatusEnum::CURRENT);
        $this->givenBricBrocProjectExists($daedalus);
        $this->givenDaedalusHasIncidentPoints($daedalus, 3);
        $this->givenRandomFloatIsZero();
        $this->givenSelectedIncidentIs(CycleIncidentEnum::OXYGEN_LEAK);
        $this->givenEquipmentInLaboratory($daedalus, EquipmentEnum::OXYGEN_TANK);

        // When
        $this->whenDispatchingCycleIncidents($daedalus);

        // Then
        $this->thenRepositoryShouldBeCalledOncePerGameEquipmentIncidentType($repository);
    }

    private function givenRepositoryThatCountsFindByNameAndDaedalusCalls(): InMemoryGameEquipmentRepository
    {
        $repository = new class extends InMemoryGameEquipmentRepository {
            public int $findByNameAndDaedalusCallCount = 0;

            public function findByNameAndDaedalus(string $name, Daedalus $daedalus): array
            {
                ++$this->findByNameAndDaedalusCallCount;

                return parent::findByNameAndDaedalus($name, $daedalus);
            }
        };
        $this->gameEquipmentRepository = $repository;
        $this->daedalusIncidentService = new DaedalusIncidentService(
            eventService: $this->eventService,
            gameEquipmentRepository: $this->gameEquipmentRepository,
            getRandomElementsFromArray: $this->getRandomElementsFromArray,
            randomService: $this->randomService,
            statusService: $this->statusService,
        );
        $this->dispatchCycleIncidents = new DispatchCycleIncidentsService(
            daedalusIncidentService: $this->daedalusIncidentService,
            d100Roll: $this->d100Roll,
            eventService: $this->eventService,
            gameEquipmentRepository: $this->gameEquipmentRepository,
            probaCollectionRandomElement: $this->probaCollectionRandomElement,
            randomFloat: $this->randomFloat,
        );

        return $repository;
    }

    private function givenADaedalusWithSingleEquipmentTypeInDistribution(): Daedalus
    {
        $daedalus = $this->givenADaedalus();
        $difficultyConfig = $daedalus->getGameConfig()->getDifficultyConfig();
        $difficultyConfig->setEquipmentBreakRateDistribution([
            EquipmentEnum::OXYGEN_TANK => 1,
        ]);

        return $daedalus;
    }

    private function thenRepositoryShouldBeCalledOncePerGameEquipmentIncidentType($repository): void
    {
        // Il y a 3 incidents GameEquipment (OXYGEN_LEAK, FUEL_LEAK, DOOR_BLOCKED) évalués dans le cycle,
        // donc 3 appels à findByNameAndDaedalus sont attendus.
        self::assertSame(3, $repository->findByNameAndDaedalusCallCount, 'findByNameAndDaedalus should be called once per GameEquipment incident type');
    }

    private function givenADaedalus(): Daedalus
    {
        return DaedalusFactory::createDaedalus();
    }

    private function givenDaedalusIsInState(Daedalus $daedalus, string $status): void
    {
        $daedalus->getDaedalusInfo()->setGameStatus($status);
    }

    private function givenBricBrocProjectIsFinished(Daedalus $daedalus): void
    {
        $bricBroc = ProjectFactory::createNeronProjectByNameForDaedalus(ProjectName::BRIC_BROC, $daedalus);
        $bricBroc->finish();
    }

    private function givenBricBrocProjectExists(Daedalus $daedalus): void
    {
        ProjectFactory::createNeronProjectByNameForDaedalus(ProjectName::BRIC_BROC, $daedalus);
    }

    private function givenD100RollIsSuccessful(): void
    {
        $this->d100Roll->makeSuccessful();
    }

    private function givenRandomFloatIsVerySmall(): void
    {
        $this->randomFloat->setResult(10 ** -3);
    }

    private function givenRandomFloatIsZero(): void
    {
        $this->randomFloat->setResult(0);
    }

    private function givenDaedalusHasIncidentPoints(Daedalus $daedalus, int $points): void
    {
        $daedalus->addIncidentPoints($points);
    }

    private function givenSelectedIncidentIs(CycleIncidentEnum $incident): void
    {
        $this->probaCollectionRandomElement->setResult($incident->value);
    }

    private function givenLaboratoryExists(Daedalus $daedalus): Place
    {
        return $daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
    }

    private function whenDispatchingCycleIncidents(Daedalus $daedalus): bool
    {
        return $this->dispatchCycleIncidents->execute($daedalus, new \DateTime());
    }

    private function thenIncidentsShouldNotBeExecuted(bool $result, ?string $message = null): void
    {
        self::assertFalse($result, $message);
    }

    private function thenRoomShouldBeBurning(Place $room): void
    {
        self::assertTrue($room->hasStatus(StatusEnum::FIRE), 'Room should burn');
    }

    private function givenEquipmentInLaboratory(Daedalus $daedalus, string $equipmentName): GameEquipment
    {
        $equipment = GameEquipmentFactory::createEquipmentByNameForHolder(
            name: $equipmentName,
            holder: $daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY)
        );
        $this->gameEquipmentRepository->save($equipment);

        return $equipment;
    }

    private function givenDoorBetweenLaboratoryAndMedlab(Daedalus $daedalus): Door
    {
        $door = Door::createFromRooms(
            $daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY),
            Place::createRoomByNameInDaedalus(RoomEnum::MEDLAB, $daedalus)
        );
        $door->setName(DoorEnum::MEDLAB_LABORATORY);
        $door->setEquipment(EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::DOOR)));
        $this->gameEquipmentRepository->save($door);

        return $door;
    }

    private function givenDoorBetweenLaboratoryAndFrontCorridor(Daedalus $daedalus): Door
    {
        $door = Door::createFromRooms(
            $daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY),
            Place::createRoomByNameInDaedalus(RoomEnum::FRONT_CORRIDOR, $daedalus)
        );
        $door->setEquipment(EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::DOOR)));
        $this->gameEquipmentRepository->save($door);

        return $door;
    }

    private function givenRandomServiceWillReturn(array $equipment): void
    {
        $this->randomService->shouldReceive('getRandomDaedalusEquipmentFromProbaCollection')->once()->andReturn($equipment);
    }

    private function givenPlayerInDaedalus(Daedalus $daedalus): Player
    {
        return PlayerFactory::createPlayerWithDaedalus($daedalus);
    }

    private function thenEquipmentShouldBeBroken(GameEquipment $equipment, string $message): void
    {
        self::assertTrue($equipment->isBroken(), $message);
    }

    private function thenEquipmentShouldNotBeBroken(GameEquipment $equipment, string $message): void
    {
        self::assertFalse($equipment->isBroken(), $message);
    }

    private function thenEventShouldBeCalledWithTag(string $tag): void
    {
        $this->eventService
            ->shouldHaveReceived('callEvent')
            ->withArgs(static fn (AbstractGameEvent $event) => $event->hasTag($tag))
            ->once();
    }
}
