<?php

namespace Mush\Tests\unit\Equipment\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
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
use Mush\Equipment\Normalizer\EquipmentNormalizer;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationService;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;
use PHPUnit\Framework\TestCase;

class EquipmentNormalizerTest extends TestCase
{
    private EquipmentNormalizer $normalizer;

    /** @var TranslationService|Mockery\Mock */
    private TranslationService $translationService;
    /** @var GearToolServiceInterface|Mockery\Mock */
    private GearToolServiceInterface $gearToolService;
    /** @var ConsumableDiseaseServiceInterface|Mockery\Mock */
    private ConsumableDiseaseServiceInterface $consumableDiseaseService;
    /** @var EquipmentEffectServiceInterface|Mockery\Mock */
    private EquipmentEffectServiceInterface $equipmentEffectService;

    /**
     * @before
     */
    public function before()
    {
        $this->translationService = \Mockery::mock(TranslationService::class);
        $this->gearToolService = \Mockery::mock(GearToolServiceInterface::class);
        $this->consumableDiseaseService = \Mockery::mock(ConsumableDiseaseServiceInterface::class);
        $this->equipmentEffectService = \Mockery::mock(EquipmentEffectServiceInterface::class);

        $this->normalizer = new EquipmentNormalizer(
            $this->translationService,
            $this->gearToolService,
            $this->consumableDiseaseService,
            $this->equipmentEffectService
        );
    }

    /**
     * @after
     */
    public function after()
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

        $equipmentConfig = new EquipmentConfig();

        $equipment = \Mockery::mock(GameEquipment::class);
        $equipment->shouldReceive('getId')->andReturn(2);
        $equipment->shouldReceive('getStatuses')->andReturn(new ArrayCollection([]));
        $equipment->shouldReceive('getHolder')->andReturn($place);
        $equipment->makePartial();

        $equipment
            ->setEquipment($equipmentConfig)
            ->setName('equipment')
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('equipment.name', [], 'equipments', LanguageEnum::FRENCH)
            ->andReturn('translated name')
            ->once()
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('equipment.description', [], 'equipments', LanguageEnum::FRENCH)
            ->andReturn('translated description')
            ->once()
        ;

        $this->gearToolService
            ->shouldReceive('getActionsTools')
            ->with($player, [ActionScopeEnum::ROOM, ActionScopeEnum::SHELVE], GameEquipment::class)
            ->andReturn(new ArrayCollection([]))
            ->once()
        ;

        $data = $this->normalizer->normalize($equipment, null, ['currentPlayer' => $player]);

        $expected = [
            'id' => 2,
            'key' => 'equipment',
            'name' => 'translated name',
            'description' => 'translated description',
            'statuses' => [],
            'actions' => [],
            'effects' => [],
        ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
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

        $equipmentConfig = new ItemConfig();

        $equipment = \Mockery::mock(GameItem::class);
        $equipment->shouldReceive('getId')->andReturn(2);
        $equipment->shouldReceive('getStatuses')->andReturn(new ArrayCollection([]));
        $equipment->shouldReceive('getHolder')->andReturn($place);
        $time = new \DateTime();
        $equipment->shouldReceive('getUpdatedAt')->andReturn($time);
        $equipment->makePartial();

        $equipment
            ->setEquipment($equipmentConfig)
            ->setName('equipment')
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('equipment.name', [], 'items', LanguageEnum::FRENCH)
            ->andReturn('translated name')
            ->once()
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('equipment.description', [], 'items', LanguageEnum::FRENCH)
            ->andReturn('translated description')
            ->once()
        ;

        $this->gearToolService
            ->shouldReceive('getActionsTools')
            ->with($player, [ActionScopeEnum::ROOM, ActionScopeEnum::SHELVE], GameItem::class)
            ->andReturn(new ArrayCollection([]))
            ->once()
        ;

        $data = $this->normalizer->normalize($equipment, null, ['currentPlayer' => $player]);

        $expected = [
            'id' => 2,
            'key' => 'equipment',
            'updatedAt' => $time,
            'name' => 'translated name',
            'description' => 'translated description',
            'statuses' => [],
            'actions' => [],
            'effects' => [],
        ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
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

        $itemConfig = new ItemConfig();
        $itemConfig->setEquipmentName(ItemEnum::NATAMY_RIFLE);
        $blueprint = new Blueprint();
        $blueprint
            ->setCraftedEquipmentName($itemConfig->getEquipmentName())
            ->setIngredients([ItemEnum::BLASTER => 1, ItemEnum::ECHOLOCATOR => 2])
        ;

        $equipmentConfig = new EquipmentConfig();
        $equipmentConfig->setMechanics(new ArrayCollection([$blueprint]));

        $equipment = \Mockery::mock(GameEquipment::class);
        $equipment->shouldReceive('getId')->andReturn(2);
        $equipment->shouldReceive('getStatuses')->andReturn(new ArrayCollection([]));
        $equipment->shouldReceive('getHolder')->andReturn($place);
        $equipment->makePartial();

        $equipment
            ->setEquipment($equipmentConfig)
            ->setName('equipment')
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('blueprint.name', ['item' => ItemEnum::NATAMY_RIFLE], 'equipments', LanguageEnum::FRENCH)
            ->andReturn('translated name')
            ->once()
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('blueprint.description', [], 'equipments', LanguageEnum::FRENCH)
            ->andReturn('translated description')
            ->once()
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('blueprint_ingredient.description', ['item' => ItemEnum::BLASTER, 'quantity' => 1], 'items', LanguageEnum::FRENCH)
            ->andReturn('ingredient 1')
            ->once()
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('blueprint_ingredient.description', ['item' => ItemEnum::ECHOLOCATOR, 'quantity' => 2], 'items', LanguageEnum::FRENCH)
            ->andReturn('ingredient 2')
            ->once()
        ;

        $this->gearToolService
            ->shouldReceive('getActionsTools')
            ->with($player, [ActionScopeEnum::ROOM, ActionScopeEnum::SHELVE], GameEquipment::class)
            ->andReturn(new ArrayCollection([]))
            ->once()
        ;

        $data = $this->normalizer->normalize($equipment, null, ['currentPlayer' => $player]);

        $expected = [
            'id' => 2,
            'key' => 'blueprint',
            'name' => 'translated name',
            'description' => 'translated description//ingredient 1//ingredient 2',
            'statuses' => [],
            'actions' => [],
            'effects' => [],
        ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
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
        $bananaTree->makePartial();

        $bananaTree->setEquipment($bananaTreeConfig);
        $bananaTree->setName(GamePlantEnum::BANANA_TREE);

        $this->translationService
            ->shouldReceive('translate')
            ->with('banana_tree.name', ['age' => ''], 'items', LanguageEnum::FRENCH)
            ->andReturn('Bananier')
            ->once()
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('banana_tree.description', [], 'items', LanguageEnum::FRENCH)
            ->andReturn('Un bananier')
            ->once()
        ;

        $this->gearToolService
            ->shouldReceive('getActionsTools')
            ->with($player, [ActionScopeEnum::ROOM, ActionScopeEnum::SHELVE], GameItem::class)
            ->andReturn(new ArrayCollection([]))
            ->once()
        ;

        $data = $this->normalizer->normalize($bananaTree, null, ['currentPlayer' => $player]);

        $expected = [
            'id' => 1,
            'key' => GamePlantEnum::BANANA_TREE,
            'updatedAt' => $time,
            'name' => 'Bananier',
            'description' => 'Un bananier',
            'statuses' => [],
            'actions' => [],
            'effects' => [],
        ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
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
        $bananaTree->makePartial();

        $bananaTree->setEquipment($bananaTreeConfig);
        $bananaTree->setName(GamePlantEnum::BANANA_TREE);

        $this->translationService
            ->shouldReceive('translate')
            ->with('banana_tree.name', ['age' => 'young'], 'items', LanguageEnum::FRENCH)
            ->andReturn('Jeune Bananier')
            ->once()
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('banana_tree.description', [], 'items', LanguageEnum::FRENCH)
            ->andReturn('Un bananier')
            ->once()
        ;

        $this->gearToolService
            ->shouldReceive('getActionsTools')
            ->with($player, [ActionScopeEnum::ROOM, ActionScopeEnum::SHELVE], GameItem::class)
            ->andReturn(new ArrayCollection([]))
            ->once()
        ;

        $data = $this->normalizer->normalize($bananaTree, null, ['currentPlayer' => $player]);

        $expected = [
            'id' => 1,
            'key' => GamePlantEnum::BANANA_TREE,
            'updatedAt' => $time,
            'name' => 'Jeune Bananier',
            'description' => 'Un bananier',
            'statuses' => [],
            'actions' => [],
            'effects' => [],
        ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
    }
}
