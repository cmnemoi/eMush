<?php

namespace Mush\Tests\unit\Equipment\CycleHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Daedalus\Service\GetHolidayForDaedalusService;
use Mush\Equipment\CycleHandler\PlantCycleHandler;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\PlantEffect;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\HolidayEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Factory\ProjectFactory;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PlantCycleHandlerTest extends TestCase
{
    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;
    private Mockery\Mock|RandomServiceInterface $randomService;
    private EventServiceInterface|Mockery\Mock $eventService;
    private EquipmentEffectServiceInterface|Mockery\Mock $equipmentEffectService;
    private Mockery\Mock|StatusServiceInterface $statusService;
    private PlantCycleHandler $plantCycleHandler;
    private Daedalus $daedalus;
    private Project $heatLamps;
    private Project $foodRetailer;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->equipmentEffectService = \Mockery::mock(EquipmentEffectServiceInterface::class);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->plantCycleHandler = new PlantCycleHandler(
            $this->eventService,
            $this->gameEquipmentService,
            $this->randomService,
            $this->equipmentEffectService,
            $this->statusService,
            new GetHolidayForDaedalusService()
        );

        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->heatLamps = ProjectFactory::createHeatLampProjectForDaedalus($this->daedalus);
        $this->foodRetailer = ProjectFactory::createNeronProjectByNameForDaedalus(name: ProjectName::FOOD_RETAILER, daedalus: $this->daedalus);

        $this->daedalus->getDaedalusConfig()->setHoliday(HolidayEnum::NONE);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testShouldCreateAnExtraFruitInGardenIfHeatLampsProjectIsFinished(): void
    {
        $place = new Place();
        $place
            ->setDaedalus($this->daedalus)
            ->setName(RoomEnum::HYDROPONIC_GARDEN);

        // given I have a plant
        $gamePlant = $this->createPlant($place);

        // given Heat Lamps project is finished
        $this->heatLamps->makeProgressAndUpdateParticipationDate(100);

        // Setup universe state
        $this->equipmentEffectService->shouldReceive('getPlantEffect')->andReturn($this->getPlantEffect());
        $this->eventService->shouldReceive('callEvent')->once();
        $this->statusService->shouldReceive('createStatusFromName')->once();

        // Given universe is in a state in which Heat Lamps will be activated
        $this->randomService->shouldReceive('isSuccessful')->with($this->heatLamps->getActivationRate())->andReturn(true);

        // Then I expect 2 fruits to be created
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->twice();

        // When a new day comes for the plant
        $this->plantCycleHandler->handleNewDay($gamePlant, new \DateTime());
    }

    public function testShouldNotCreateAnExtraFruitWithHeatLampsProjectIfNotInGarden(): void
    {
        $place = new Place();
        $place
            ->setDaedalus($this->daedalus)
            ->setName(RoomEnum::LABORATORY);

        // given I have a plant
        $gamePlant = $this->createPlant($place);

        // given Heat Lamps project is finished
        $this->heatLamps->finish();

        // Setup universe state
        $this->equipmentEffectService->shouldReceive('getPlantEffect')->andReturn($this->getPlantEffect());
        $this->eventService->shouldReceive('callEvent')->once();
        $this->statusService->shouldReceive('createStatusFromName')->once();

        // Given universe is a state in which Heat Lamps will be activated
        $this->randomService->shouldReceive('isSuccessful')->with($this->heatLamps->getActivationRate())->andReturn(true);

        // Then I expect 1 fruits to be created
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();

        // When a new day comes for the plant
        $this->plantCycleHandler->handleNewDay($gamePlant, new \DateTime());
    }

    public function testShouldCreateJumpkinFruitFromBananaTreeIfHalloweenEvent(): void
    {
        $place = Place::createRoomByNameInDaedalus(name: RoomEnum::LABORATORY, daedalus: $this->daedalus);

        // given I have a banana tree
        $gamePlant = $this->createPlant($place, name: GamePlantEnum::BANANA_TREE);

        // given the daedalus is in a halloween event
        $this->daedalus->getDaedalusConfig()->setHoliday(HolidayEnum::HALLOWEEN);

        // Setup universe state
        $this->equipmentEffectService->shouldReceive('getPlantEffect')->andReturn($this->getPlantEffect());
        $this->eventService->shouldReceive('callEvent')->once();
        $this->statusService->shouldReceive('createStatusFromName')->once();

        // given universe state allows jumpkin fruit creation
        $this->randomService->shouldReceive('isDoubleRollSuccessful')->with(5)->andReturn(true);

        // Then I expect 2 fruits to be created : 1 normal and 1 jumpkin
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->withArgs(
            static fn ($fruitName) => $fruitName === GameFruitEnum::JUMPKIN
        )->once();

        // When a new day comes for the plant
        $this->plantCycleHandler->handleNewDay($gamePlant, new \DateTime());
    }

    /**
     * @dataProvider provideShouldCreateJumpkinFruitFromAlienPlantIfHalloweenEventCases
     */
    public function testShouldCreateJumpkinFruitFromAlienPlantIfHalloweenEvent(string $plantName): void
    {
        $place = Place::createRoomByNameInDaedalus(name: RoomEnum::LABORATORY, daedalus: $this->daedalus);

        // given I have an alien plant
        $gamePlant = $this->createPlant($place, name: $plantName);

        // given the daedalus is in a halloween event
        $this->daedalus->getDaedalusConfig()->setHoliday(HolidayEnum::HALLOWEEN);

        // Setup universe state
        $this->equipmentEffectService->shouldReceive('getPlantEffect')->andReturn($this->getPlantEffect());
        $this->eventService->shouldReceive('callEvent')->once();
        $this->statusService->shouldReceive('createStatusFromName')->once();

        // given universe state allows jumpkin fruit creation
        $this->randomService->shouldReceive('isDoubleRollSuccessful')->with(20)->andReturn(true);

        // Then I expect 2 fruits to be created : 1 normal and 1 jumpkin
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->withArgs(
            static fn ($fruitName) => $fruitName === GameFruitEnum::JUMPKIN
        )->once();

        // When a new day comes for the plant
        $this->plantCycleHandler->handleNewDay($gamePlant, new \DateTime());
    }

    public static function provideShouldCreateJumpkinFruitFromAlienPlantIfHalloweenEventCases(): iterable
    {
        return array_map(
            static fn (string $plantName) => ['plantName' => $plantName],
            GamePlantEnum::getAlienPlants()
        );
    }

    private function createPlant(EquipmentHolderInterface $holder, string $name = 'plant name'): GameItem
    {
        $newFruit = new ItemConfig();
        $newFruit->setEquipmentName('fruit name');

        $gameFruit = new GameItem(new Place());
        $gameFruit->setEquipment($newFruit);

        $plant = new ItemConfig();
        $plant
            ->setEquipmentName('plant name');
        $plantType = new Plant();
        $plantType->setFruitName($newFruit->getEquipmentName());

        $plant->setMechanics(new ArrayCollection([$plantType]));

        $gamePlant = new GameItem($holder);
        $gamePlant
            ->setName($name)
            ->setEquipment($plant);

        return $gamePlant;
    }

    private function getPlantEffect(): PlantEffect
    {
        $plantEffect = new PlantEffect();
        $plantEffect
            ->setMaturationTime(10)
            ->setOxygen(10);

        return $plantEffect;
    }
}
