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

        $resultEquipment = new ItemConfig();
        $resultEquipment->setEquipmentName(ItemEnum::NATAMY_RIFLE);
        $blueprint = new Blueprint();
        $blueprint
            ->setCraftedEquipmentName($resultEquipment->getEquipmentName())
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
}
