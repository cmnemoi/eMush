<?php

namespace Mush\Tests\unit\Equipment\CycleHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\CycleHandler\PlantCycleHandler;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\PlantEffect;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\ConfigData\DifficultyConfigData;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\HolidayEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Factory\ProjectFactory;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
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
        );

        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->heatLamps = ProjectFactory::createHeatLampProjectForDaedalus($this->daedalus);
        $this->foodRetailer = ProjectFactory::createNeronProjectByNameForDaedalus(name: ProjectName::FOOD_RETAILER, daedalus: $this->daedalus);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testNewCycle(): void
    {
        $plant = new ItemConfig();

        $plantType = new Plant();
        $plant->setMechanics(new ArrayCollection([$plantType]));

        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once(); // Plant should not get disease

        $difficultyConfig = DifficultyConfig::fromDto(DifficultyConfigData::getByName('default'));
        $difficultyConfig->setPlantDiseaseRate(50);
        $gameConfig = new GameConfig();
        $gameConfig->setDifficultyConfig($difficultyConfig);
        $this->daedalus->getDaedalusInfo()->setGameConfig($gameConfig);

        $place = new Place();
        $place
            ->setDaedalus($this->daedalus)
            ->setName(RoomEnum::LABORATORY);

        $gamePlant = new GameItem($place);
        $gamePlant->setEquipment($plant);

        $chargeStatusConfig = new ChargeStatusConfig();
        $chargeStatusConfig->setStatusName(EquipmentStatusEnum::PLANT_YOUNG);

        $chargeStatus = new ChargeStatus($gamePlant, $chargeStatusConfig);
        $chargeStatus->setCharge(1);

        $plantEffect = new PlantEffect();
        $plantEffect
            ->setMaturationTime(10)
            ->setOxygen(10);

        $this->plantCycleHandler->handleNewCycle($gamePlant, new \DateTime());

        self::assertFalse(
            $gamePlant
                ->getStatuses()
                ->filter(static fn (Status $status) => EquipmentStatusEnum::PLANT_YOUNG === $status->getName())
                ->isEmpty()
        );
        self::assertTrue(
            $gamePlant
                ->getStatuses()
                ->filter(static fn (Status $status) => EquipmentStatusEnum::PLANT_DISEASED === $status->getName())
                ->isEmpty()
        );
    }

    public function testNewCycleGetDiseaseAndGrow(): void
    {
        $plant = new ItemConfig();

        $plantType = new Plant();
        $plant->setMechanics(new ArrayCollection([$plantType]));

        $difficultyConfig = DifficultyConfig::fromDto(DifficultyConfigData::getByName('default'));
        $difficultyConfig->setPlantDiseaseRate(50);
        $gameConfig = new GameConfig();
        $gameConfig->setDifficultyConfig($difficultyConfig);
        $this->daedalus->getDaedalusInfo()->setGameConfig($gameConfig);

        $place = new Place();
        $place
            ->setDaedalus($this->daedalus)
            ->setName(RoomEnum::LABORATORY);

        $gamePlant = new GameItem($place);
        $gamePlant->setEquipment($plant);

        $chargeStatusConfig = new ChargeStatusConfig();
        $chargeStatusConfig->setStatusName(EquipmentStatusEnum::PLANT_YOUNG);
        $chargeStatus = new ChargeStatus($gamePlant, $chargeStatusConfig);
        $chargeStatus->setCharge(1);

        // Plant get disease and grow
        $chargeStatus->setCharge(10);

        $plantEffect = new PlantEffect();
        $plantEffect
            ->setMaturationTime(10)
            ->setOxygen(10);

        $this->equipmentEffectService->shouldReceive('getPlantEffect')->andReturn($plantEffect);
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->statusService->shouldReceive('createStatusFromName')->once();

        $this->plantCycleHandler->handleNewCycle($gamePlant, new \DateTime());

        self::assertCount(1, $gamePlant->getStatuses());
    }

    public function testNewDayPlantHealthy(): void
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setMaxOxygen(32)->setInitOxygen(10);
        $this->daedalus->setDaedalusVariables($daedalusConfig);
        $player = new Player();
        $player->setDaedalus($this->daedalus);
        $room = new Place();
        $room->setName(RoomEnum::LABORATORY);
        $room->addPlayer($player);
        $room->setDaedalus($this->daedalus);

        $time = new \DateTime();

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

        $plantEffect = new PlantEffect();
        $plantEffect
            ->setMaturationTime(10)
            ->setOxygen(10);

        $gamePlant = new GameItem($room);
        $gamePlant
            ->setName('plant name')
            ->setEquipment($plant);

        $this->equipmentEffectService->shouldReceive('getPlantEffect')->andReturn($plantEffect);
        $this->gameEquipmentService->shouldReceive('persist');

        $this->statusService->shouldReceive('createStatusFromName')->once();
        $this->statusService->shouldReceive('removeStatus')->never();

        $this->eventService->shouldReceive('callEvent')
            ->withArgs(fn (AbstractGameEvent $event) => $event instanceof DaedalusVariableEvent
                && $event->getDaedalus() === $this->daedalus
                && $event->getRoundedQuantity() === 10)
            ->once();

        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();

        // Mature Plant, no problem
        $this->plantCycleHandler->handleNewDay($gamePlant, $time);

        self::assertCount(1, $room->getEquipments());
    }

    public function testNewDayPlantThirsty(): void
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setMaxOxygen(32)->setInitOxygen(10);
        $this->daedalus = new Daedalus();
        $this->daedalus->setDaedalusVariables($daedalusConfig);
        $player = new Player();
        $player->setDaedalus($this->daedalus);
        $room = new Place();
        $room->addPlayer($player);
        $room->setDaedalus($this->daedalus);

        $newFruit = new ItemConfig();
        $newFruit->setEquipmentName('fruit name');

        $this->gameEquipmentService->shouldReceive('persist');

        $plant = new ItemConfig();
        $plant
            ->setEquipmentName('plant name');
        $plantType = new Plant();
        $plantType->setFruitName($newFruit->getEquipmentName());

        $plant->setMechanics(new ArrayCollection([$plantType]));

        $plantEffect = new PlantEffect();
        $plantEffect
            ->setMaturationTime(10)
            ->setOxygen(10);
        $this->equipmentEffectService->shouldReceive('getPlantEffect')->andReturn($plantEffect);

        $gamePlant = new GameItem($room);
        $gamePlant
            ->setName('plant name')
            ->setEquipment($plant);
        $thirstyConfig = new StatusConfig();
        $thirstyConfig->setStatusName(EquipmentStatusEnum::PLANT_THIRSTY);
        $status = new Status($gamePlant, $thirstyConfig);

        $this->statusService->shouldReceive('createStatusFromName')->once();
        $this->statusService->shouldReceive('removeStatus')->once();

        $this->eventService->shouldReceive('callEvent')
            ->withArgs(fn (AbstractGameEvent $event) => $event instanceof DaedalusVariableEvent
                && $event->getDaedalus() === $this->daedalus
                && $event->getRoundedQuantity() === 10)
            ->once();

        // Thirsty plant
        $this->plantCycleHandler->handleNewDay($gamePlant, new \DateTime());

        self::assertCount(1, $room->getEquipments());

        $this->gameEquipmentService->shouldReceive('createEquipment')->andReturn(new GameItem(new Place()));
    }

    public function testNewDayPlantDry(): void
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setMaxOxygen(32)->setInitOxygen(10);
        $this->daedalus = new Daedalus();
        $this->daedalus->setDaedalusVariables($daedalusConfig);
        $player = new Player();
        $player->setDaedalus($this->daedalus);
        $room = new Place();
        $room->addPlayer($player);
        $room->setDaedalus($this->daedalus);

        $time = new \DateTime();

        $newFruit = new ItemConfig();
        $newFruit->setEquipmentName('fruit name');

        $plant = new ItemConfig();
        $plant
            ->setEquipmentName('plant name');
        $plantType = new Plant();
        $plantType->setFruitName($newFruit->getEquipmentName());

        $plant->setMechanics(new ArrayCollection([$plantType]));

        $plantEffect = new PlantEffect();
        $plantEffect
            ->setMaturationTime(10)
            ->setOxygen(10);

        $gamePlant = new GameItem($room);
        $gamePlant
            ->setName('plant name')
            ->setEquipment($plant);

        $dryConfig = new StatusConfig();
        $dryConfig->setStatusName(EquipmentStatusEnum::PLANT_DRY);
        $status = new Status($gamePlant, $dryConfig);

        $this->equipmentEffectService->shouldReceive('getPlantEffect')->andReturn($plantEffect);

        $this->eventService->shouldReceive('callEvent')
            ->withArgs(
                static fn (AbstractGameEvent $event) => $event instanceof EquipmentEvent
                && $event->getGameEquipment() === $gamePlant
            )->once();
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();

        // Dried out plant
        // @TODO $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName');
        $this->plantCycleHandler->handleNewDay($gamePlant, $time);

        self::assertCount(1, $room->getEquipments());
        self::assertNotContains($plant, $room->getEquipments());
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

    public function testShouldCreateJumpkinFruitIfHalloweenEvent(): void
    {
        $place = new Place();
        $place
            ->setDaedalus($this->daedalus)
            ->setName(RoomEnum::LABORATORY);

        // given I have a plant
        $gamePlant = $this->createPlant($place);

        // given the daedalus is in a halloween event
        $this->daedalus->getDaedalusConfig()->setHoliday(HolidayEnum::HALLOWEEN);

        // Setup universe state
        $this->equipmentEffectService->shouldReceive('getPlantEffect')->andReturn($this->getPlantEffect());
        $this->eventService->shouldReceive('callEvent')->once();
        $this->statusService->shouldReceive('createStatusFromName')->once();

        // given universe state allows jumpkin fruit creation
        $this->randomService->shouldReceive('rollTwiceAndAverage')->with(1, 100)->andReturn(100);

        // Then I expect 2 fruits to be created : 1 normal and 1 jumpkin
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->withArgs(
            static fn ($fruitName) => $fruitName === GameFruitEnum::JUMPKIN
        )->once();

        // When a new day comes for the plant
        $this->plantCycleHandler->handleNewDay($gamePlant, new \DateTime());
    }

    private function createPlant(EquipmentHolderInterface $holder): GameItem
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
            ->setName('plant name')
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
