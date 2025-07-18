<?php

namespace Mush\Tests\unit\Equipment\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Disease\Service\ConsumableDiseaseServiceInterface;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\Normalizer\EquipmentNormalizer;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationService;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class EquipmentNormalizerTest extends TestCase
{
    private EquipmentNormalizer $normalizer;

    /** @var Mockery\Mock|TranslationService */
    private TranslationService $translationService;

    /** @var ConsumableDiseaseServiceInterface|Mockery\Mock */
    private ConsumableDiseaseServiceInterface $consumableDiseaseService;

    /** @var EquipmentEffectServiceInterface|Mockery\Mock */
    private EquipmentEffectServiceInterface $equipmentEffectService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->translationService = \Mockery::mock(TranslationService::class);
        $this->consumableDiseaseService = \Mockery::mock(ConsumableDiseaseServiceInterface::class);
        $this->equipmentEffectService = \Mockery::mock(EquipmentEffectServiceInterface::class);

        $this->normalizer = new EquipmentNormalizer(
            $this->consumableDiseaseService,
            $this->equipmentEffectService,
            self::createStub(RebelBaseRepositoryInterface::class),
            $this->translationService,
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testEquipmentNormalizer()
    {
        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $place = new Place();
        $player = new Player();
        $player->setDaedalus($daedalus);
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());

        $equipmentConfig = new EquipmentConfig();

        $equipment = \Mockery::mock(GameEquipment::class);
        $equipment->shouldReceive('getId')->andReturn(2);
        $equipment->shouldReceive('getStatuses')->andReturn(new ArrayCollection([]));
        $equipment->shouldReceive('getHolder')->andReturn($place);
        $equipment->shouldReceive('getPlace')->andReturn($place);
        $equipment->shouldReceive('getClassName')->andReturn(GameEquipment::class);
        $equipment->makePartial();

        $equipment
            ->setEquipment($equipmentConfig)
            ->setName('equipment');

        $this->translationService
            ->shouldReceive('translate')
            ->with('equipment.name', [], 'equipments', LanguageEnum::FRENCH)
            ->andReturn('translated name')
            ->once();

        $this->translationService
            ->shouldReceive('translate')
            ->with('equipment.description', [], 'equipments', LanguageEnum::FRENCH)
            ->andReturn('translated description')
            ->once();

        $data = $this->normalizer->normalize($equipment, null, ['currentPlayer' => $player]);

        $expected = [
            'id' => 2,
            'key' => 'equipment',
            'name' => 'translated name',
            'description' => 'translated description',
            'statuses' => [],
            'actions' => [],
            'effects' => [],
            'skins' => [],
        ];

        self::assertIsArray($data);
        self::assertSame($expected, $data);
    }

    public function testItemNormalizer()
    {
        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $place = new Place();
        $player = new Player();
        $player->setDaedalus($daedalus);
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());

        $equipmentConfig = new ItemConfig();

        $equipment = \Mockery::mock(GameItem::class);
        $equipment->shouldReceive('getId')->andReturn(2);
        $equipment->shouldReceive('getStatuses')->andReturn(new ArrayCollection([]));
        $equipment->shouldReceive('getHolder')->andReturn($place);
        $time = new \DateTime();
        $equipment->shouldReceive('getUpdatedAt')->andReturn($time);
        $equipment->shouldReceive('getClassName')->andReturn(GameItem::class);
        $equipment->makePartial();

        $equipment
            ->setEquipment($equipmentConfig)
            ->setName('equipment');

        $this->translationService
            ->shouldReceive('translate')
            ->with('equipment.name', [], 'items', LanguageEnum::FRENCH)
            ->andReturn('translated name')
            ->once();

        $this->translationService
            ->shouldReceive('translate')
            ->with('equipment.description', [], 'items', LanguageEnum::FRENCH)
            ->andReturn('translated description')
            ->once();

        $data = $this->normalizer->normalize($equipment, null, ['currentPlayer' => $player]);

        $expected = [
            'id' => 2,
            'key' => 'equipment',
            'name' => 'translated name',
            'description' => 'translated description',
            'statuses' => [],
            'actions' => [],
            'effects' => [],
            'skins' => [],
            'updatedAt' => $time,
        ];

        self::assertIsArray($data);
        self::assertSame($expected, $data);
    }

    public function testBlueprintNormalizer()
    {
        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $place = new Place();
        $player = new Player();
        $player->setDaedalus($daedalus);
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());

        $itemConfig = new ItemConfig();
        $itemConfig->setEquipmentName(ItemEnum::NATAMY_RIFLE);
        $blueprint = new Blueprint();
        $blueprint
            ->setCraftedEquipmentName($itemConfig->getEquipmentName())
            ->setIngredients([ItemEnum::BLASTER => 1, ItemEnum::ECHOLOCATOR => 2]);

        $equipmentConfig = new EquipmentConfig();
        $equipmentConfig->setMechanics(new ArrayCollection([$blueprint]));

        $equipment = \Mockery::mock(GameEquipment::class);
        $equipment->shouldReceive('getId')->andReturn(2);
        $equipment->shouldReceive('getStatuses')->andReturn(new ArrayCollection([]));
        $equipment->shouldReceive('getHolder')->andReturn($place);
        $equipment->shouldReceive('getPlace')->andReturn($place);
        $equipment->shouldReceive('getClassName')->andReturn(GameEquipment::class);
        $equipment->makePartial();

        $equipment
            ->setEquipment($equipmentConfig)
            ->setName('equipment');

        $this->translationService
            ->shouldReceive('translate')
            ->with('blueprint.name', ['item' => ItemEnum::NATAMY_RIFLE], 'equipments', LanguageEnum::FRENCH)
            ->andReturn('translated name')
            ->once();

        $this->translationService
            ->shouldReceive('translate')
            ->with('blueprint.description', [], 'equipments', LanguageEnum::FRENCH)
            ->andReturn('translated description')
            ->once();

        $this->translationService
            ->shouldReceive('translate')
            ->with('blueprint_ingredient.description', ['item' => ItemEnum::BLASTER, 'quantity' => 1], 'items', LanguageEnum::FRENCH)
            ->andReturn('ingredient 1')
            ->once();

        $this->translationService
            ->shouldReceive('translate')
            ->with('blueprint_ingredient.description', ['item' => ItemEnum::ECHOLOCATOR, 'quantity' => 2], 'items', LanguageEnum::FRENCH)
            ->andReturn('ingredient 2')
            ->once();

        $data = $this->normalizer->normalize($equipment, null, ['currentPlayer' => $player]);

        $expected = [
            'id' => 2,
            'key' => 'blueprint',
            'name' => 'translated name',
            'description' => 'translated description//ingredient 1//ingredient 2',
            'statuses' => [],
            'actions' => [],
            'effects' => [],
            'skins' => [],
        ];

        self::assertIsArray($data);
        self::assertSame($expected, $data);
    }

    public function testPlantNormalizer(): void
    {
        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $place = new Place();
        $player = new Player();
        $player->setDaedalus($daedalus);
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());

        $bananaTreeConfig = new ItemConfig();
        $bananaTreeConfig->setEquipmentName(GamePlantEnum::BANANA_TREE);
        $plantMechanic = new Plant();
        $plantMechanic->setFruitName(GameFruitEnum::BANANA);
        $plantMechanic->setMaturationTime([36 => 1]);
        $plantMechanic->setOxygen([1 => 1]);
        $bananaTreeConfig->setMechanics(new ArrayCollection([$plantMechanic]));

        $bananaTree = \Mockery::mock(GameItem::class);
        $bananaTree->shouldReceive('getId')->andReturn(1);
        $bananaTree->shouldReceive('getStatuses')->andReturn(new ArrayCollection([]));
        $bananaTree->shouldReceive('getHolder')->andReturn($place);
        $bananaTree->shouldReceive('getMechanics')->andReturn(new ArrayCollection([$plantMechanic]));
        $bananaTree->shouldReceive('hasStatus')->with(EquipmentStatusEnum::PLANT_YOUNG)->andReturn(false);
        $time = new \DateTime();
        $bananaTree->shouldReceive('getUpdatedAt')->andReturn($time);
        $bananaTree->shouldReceive('getClassName')->andReturn(GameItem::class);
        $bananaTree->makePartial();

        $bananaTree->setEquipment($bananaTreeConfig);
        $bananaTree->setName(GamePlantEnum::BANANA_TREE);

        $this->translationService
            ->shouldReceive('translate')
            ->with('banana_tree.name', ['age' => ''], 'items', LanguageEnum::FRENCH)
            ->andReturn('Bananier')
            ->once();

        $this->translationService
            ->shouldReceive('translate')
            ->with('banana_tree.description', [], 'items', LanguageEnum::FRENCH)
            ->andReturn('Un bananier')
            ->once();

        $data = $this->normalizer->normalize($bananaTree, null, ['currentPlayer' => $player]);

        $expected = [
            'id' => 1,
            'key' => GamePlantEnum::BANANA_TREE,
            'name' => 'Bananier',
            'description' => 'Un bananier',
            'statuses' => [],
            'actions' => [],
            'effects' => [],
            'skins' => [],
            'updatedAt' => $time,
        ];

        self::assertIsArray($data);
        self::assertSame($expected, $data);
    }

    public function testYoungPlantNormalizer(): void
    {
        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $place = new Place();
        $player = new Player();
        $player->setDaedalus($daedalus);
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());

        $bananaTreeConfig = new ItemConfig();
        $bananaTreeConfig->setEquipmentName(GamePlantEnum::BANANA_TREE);
        $plantMechanic = new Plant();
        $plantMechanic->setFruitName(GameFruitEnum::BANANA);
        $plantMechanic->setMaturationTime([36 => 1]);
        $plantMechanic->setOxygen([1 => 1]);
        $bananaTreeConfig->setMechanics(new ArrayCollection([$plantMechanic]));

        $bananaTree = \Mockery::mock(GameItem::class);
        $bananaTree->shouldReceive('getId')->andReturn(1);
        $bananaTree->shouldReceive('getStatuses')->andReturn(new ArrayCollection([]));
        $bananaTree->shouldReceive('getHolder')->andReturn($place);
        $bananaTree->shouldReceive('getMechanics')->andReturn(new ArrayCollection([$plantMechanic]));
        $bananaTree->shouldReceive('hasStatus')->with(EquipmentStatusEnum::PLANT_YOUNG)->andReturn(true);
        $time = new \DateTime();
        $bananaTree->shouldReceive('getUpdatedAt')->andReturn($time);
        $bananaTree->shouldReceive('getClassName')->andReturn(GameItem::class);
        $bananaTree->makePartial();

        $bananaTree->setEquipment($bananaTreeConfig);
        $bananaTree->setName(GamePlantEnum::BANANA_TREE);

        $this->translationService
            ->shouldReceive('translate')
            ->with('banana_tree.name', ['age' => 'young'], 'items', LanguageEnum::FRENCH)
            ->andReturn('Jeune Bananier')
            ->once();

        $this->translationService
            ->shouldReceive('translate')
            ->with('banana_tree.description', [], 'items', LanguageEnum::FRENCH)
            ->andReturn('Un bananier')
            ->once();

        $data = $this->normalizer->normalize($bananaTree, null, ['currentPlayer' => $player]);

        $expected = [
            'id' => 1,
            'key' => GamePlantEnum::BANANA_TREE,
            'name' => 'Jeune Bananier',
            'description' => 'Un bananier',
            'statuses' => [],
            'actions' => [],
            'effects' => [],
            'skins' => [],
            'updatedAt' => $time,
        ];

        self::assertIsArray($data);
        self::assertSame($expected, $data);
    }

    /**
     * @dataProvider provideShouldNormalizeDroneUpgradesCases
     */
    public function testShouldNormalizeDroneUpgrades(string $upgrade): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);

        $drone = GameEquipmentFactory::createDroneForHolder($player);
        StatusFactory::createStatusByNameForHolder($upgrade, $drone);

        $this->translationService
            ->shouldReceive('translate')
            ->with('support_drone.description', [], 'items', LanguageEnum::FRENCH)
            ->andReturn('Support drone description')
            ->once();

        $this->translationService
            ->shouldReceive('translate')
            ->with($upgrade . '.description', [], 'status', LanguageEnum::FRENCH)
            ->andReturn($upgrade)
            ->once();

        $this->translationService->shouldIgnoreMissing();

        $this->normalizer->setNormalizer(self::createStub(NormalizerInterface::class));
        $normalizedDrone = $this->normalizer->normalize($drone, null, ['currentPlayer' => $player]);

        self::assertEquals(
            "Support drone description//{$upgrade}",
            $normalizedDrone['description']
        );
    }

    public static function provideShouldNormalizeDroneUpgradesCases(): iterable
    {
        return [
            [EquipmentStatusEnum::TURBO_DRONE_UPGRADE],
            [EquipmentStatusEnum::PILOT_DRONE_UPGRADE],
            [EquipmentStatusEnum::SENSOR_DRONE_UPGRADE],
            [EquipmentStatusEnum::FIREFIGHTER_DRONE_UPGRADE],
        ];
    }

    public function testShouldNormalizeDroneWithNoUpgrades(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);

        $drone = GameEquipmentFactory::createDroneForHolder($player);

        $this->translationService
            ->shouldReceive('translate')
            ->with('support_drone.description', [], 'items', LanguageEnum::FRENCH)
            ->andReturn('Support drone description')
            ->once();

        $this->translationService->shouldIgnoreMissing();

        $this->normalizer->setNormalizer(self::createStub(NormalizerInterface::class));
        $normalizedDrone = $this->normalizer->normalize($drone, null, ['currentPlayer' => $player]);

        self::assertEquals('Support drone description', $normalizedDrone['description']);
    }
}
