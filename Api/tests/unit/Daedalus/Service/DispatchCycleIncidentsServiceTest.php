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
use Mush\Status\Enum\StatusEnum;
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
        $this->thenEquipmentShouldNotBeBroken($door, 'Door should not be broken');
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
