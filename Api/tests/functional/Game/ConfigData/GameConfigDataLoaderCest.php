<?php

namespace Mush\Tests\functional\Game\ConfigData;

use Mush\Action\ConfigData\ActionDataLoader;
use Mush\Daedalus\ConfigData\DaedalusConfigData;
use Mush\Daedalus\ConfigData\DaedalusConfigDataLoader;
use Mush\Daedalus\ConfigData\RandomItemPlacesDataLoader;
use Mush\Disease\ConfigData\ConsumableDiseaseConfigData;
use Mush\Disease\ConfigData\ConsumableDiseaseConfigDataLoader;
use Mush\Disease\ConfigData\DiseaseCauseConfigData;
use Mush\Disease\ConfigData\DiseaseCauseConfigDataLoader;
use Mush\Disease\ConfigData\DiseaseConfigData;
use Mush\Disease\ConfigData\DiseaseConfigDataLoader;
use Mush\Disease\ConfigData\SymptomActivationRequirementDataLoader;
use Mush\Disease\ConfigData\SymptomConfigDataLoader;
use Mush\Disease\Entity\Config\SymptomActivationRequirement;
use Mush\Equipment\ConfigData\BlueprintDataLoader;
use Mush\Equipment\ConfigData\BookDataLoader;
use Mush\Equipment\ConfigData\DocumentDataLoader;
use Mush\Equipment\ConfigData\DrugDataLoader;
use Mush\Equipment\ConfigData\EquipmentConfigData;
use Mush\Equipment\ConfigData\EquipmentConfigDataLoader;
use Mush\Equipment\ConfigData\FruitDataLoader;
use Mush\Equipment\ConfigData\GearDataLoader;
use Mush\Equipment\ConfigData\ItemConfigDataLoader;
use Mush\Equipment\ConfigData\PatrolShipDataLoader;
use Mush\Equipment\ConfigData\PlantDataLoader;
use Mush\Equipment\ConfigData\RationDataLoader;
use Mush\Equipment\ConfigData\ToolDataLoader;
use Mush\Equipment\ConfigData\WeaponDataLoader;
use Mush\Game\ConfigData\DifficultyConfigData;
use Mush\Game\ConfigData\DifficultyConfigDataLoader;
use Mush\Game\ConfigData\GameConfigDataLoader;
use Mush\Game\ConfigData\TriumphConfigData;
use Mush\Game\ConfigData\TriumphConfigDataLoader;
use Mush\Game\ConfigData\VariableEventConfigDataLoader;
use Mush\Game\Entity\GameConfig;
use Mush\Hunter\ConfigData\HunterConfigData;
use Mush\Hunter\ConfigData\HunterConfigDataLoader;
use Mush\Modifier\ConfigData\DirectModifierConfigDataLoader;
use Mush\Modifier\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\ConfigData\TriggerEventModifierConfigDataLoader;
use Mush\Modifier\ConfigData\VariableEventModifierConfigDataLoader;
use Mush\Place\ConfigData\PlaceConfigDataLoader;
use Mush\Player\ConfigData\CharacterConfigDataLoader;
use Mush\Status\ConfigData\ChargeStatusConfigDataLoader;
use Mush\Status\ConfigData\StatusConfigData;
use Mush\Status\ConfigData\StatusConfigDataLoader;
use Mush\Tests\FunctionalTester;

class GameConfigDataLoaderCest
{
    private GameConfigDataLoader $gameConfigDataLoader;

    // StatusConfig + dependencies
    private ModifierActivationRequirementDataLoader $modifierActivationRequirementDataLoader;
    private VariableEventModifierConfigDataLoader $triggerEventModifierConfigDataLoader;
    private ChargeStatusConfigDataLoader $chargeStatusConfigDataLoader;
    private StatusConfigDataLoader $statusConfigDataLoader;

    // DiseaseConfig + dependencies
    private SymptomActivationRequirement $symptomActivationRequirement;
    private SymptomConfigDataLoader $symptomConfigDataLoader;
    private DiseaseConfigDataLoader $diseaseConfigDataLoader;

    // EquipmentConfig + dependencies
    private ActionDataLoader $actionDataLoader;
    private BlueprintDataLoader $blueprintDataLoader;
    private BookDataLoader $bookDataLoader;
    private DocumentDataLoader $documentDataLoader;
    private DrugDataLoader $drugDataLoader;
    private FruitDataLoader $fruitDataLoader;
    private GearDataLoader $gearDataLoader;
    private PlantDataLoader $plantDataLoader;
    private RationDataLoader $rationDataLoader;
    private ToolDataLoader $toolDataLoader;
    private WeaponDataLoader $weaponDataLoader;
    private PatrolShipDataLoader $patrolShipDataLoader;
    private EquipmentConfigDataLoader $equipmentConfigDataLoader;
    private ItemConfigDataLoader $itemConfigDataLoader;

    // CharacterConfig needs everything above
    private CharacterConfigDataLoader $characterConfigDataLoader;

    // DaedalusConfig + dependencies
    private RandomItemPlacesDataLoader $randomItemPlacesDataLoader;
    private PlaceConfigDataLoader $placeConfigDataLoader;
    private DaedalusConfigDataLoader $daedalusConfigDataLoader;

    // Do not have dependencies
    private DifficultyConfigDataLoader $difficultyConfigDataLoader;
    private TriumphConfigDataLoader $triumphConfigDataLoader;
    private DiseaseCauseConfigDataLoader $diseaseCauseConfigDataLoader;
    private ConsumableDiseaseConfigDataLoader $consumableDiseaseConfigDataLoader;
    private HunterConfigDataLoader $hunterConfigDataLoader;

    private array $dependenciesDataLoaders = [];

    public function _before(FunctionalTester $I)
    {
        $this->dependenciesDataLoaders = [
            $I->grabService(VariableEventConfigDataLoader::class),
            $I->grabService(ModifierActivationRequirementDataLoader::class),
            $I->grabService(VariableEventModifierConfigDataLoader::class),
            $I->grabService(TriggerEventModifierConfigDataLoader::class),
            $I->grabService(DirectModifierConfigDataLoader::class),
            $I->grabService(ChargeStatusConfigDataLoader::class),
            $I->grabService(StatusConfigDataLoader::class),
            $I->grabService(SymptomActivationRequirementDataLoader::class),
            $I->grabService(SymptomConfigDataLoader::class),
            $I->grabService(DiseaseConfigDataLoader::class),
            $I->grabService(ActionDataLoader::class),
            $I->grabService(BlueprintDataLoader::class),
            $I->grabService(BookDataLoader::class),
            $I->grabService(DocumentDataLoader::class),
            $I->grabService(DrugDataLoader::class),
            $I->grabService(FruitDataLoader::class),
            $I->grabService(GearDataLoader::class),
            $I->grabService(PlantDataLoader::class),
            $I->grabService(RationDataLoader::class),
            $I->grabService(ToolDataLoader::class),
            $I->grabService(WeaponDataLoader::class),
            $I->grabService(PatrolShipDataLoader::class),
            $I->grabService(EquipmentConfigDataLoader::class),
            $I->grabService(ItemConfigDataLoader::class),
            $I->grabService(CharacterConfigDataLoader::class),
            $I->grabService(RandomItemPlacesDataLoader::class),
            $I->grabService(PlaceConfigDataLoader::class),
            $I->grabService(DaedalusConfigDataLoader::class),
            $I->grabService(DifficultyConfigDataLoader::class),
            $I->grabService(TriumphConfigDataLoader::class),
            $I->grabService(DiseaseCauseConfigDataLoader::class),
            $I->grabService(ConsumableDiseaseConfigDataLoader::class),
            $I->grabService(HunterConfigDataLoader::class),
        ];

        foreach ($this->dependenciesDataLoaders as $dataLoader) {
            $dataLoader->loadConfigsData();
        }

        $this->gameConfigDataLoader = $I->grabService(GameConfigDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->gameConfigDataLoader->loadConfigsData();

        // check default game config is loaded
        $I->seeInRepository(GameConfig::class, [
            'name' => 'default',
        ]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => 'default']);

        // check all child configs are associated with the game config
        $I->assertCount(count(DifficultyConfigData::$dataArray), [$gameConfig->getDifficultyConfig()]);
        $I->assertCount(count(TriumphConfigData::$dataArray), $gameConfig->getTriumphConfig());
        $I->assertCount(count(DiseaseCauseConfigData::$dataArray), $gameConfig->getDiseaseCauseConfig());
        $I->assertCount(count(ConsumableDiseaseConfigData::$dataArray), $gameConfig->getConsumableDiseaseConfig());
        $I->assertCount(count(DaedalusConfigData::$dataArray), [$gameConfig->getDaedalusConfig()]);
        $I->assertCount(16, $gameConfig->getCharactersConfig()); // 18 characters exist, but only 16 per game config
        $I->assertCount(count(StatusConfigData::$dataArray), $gameConfig->getStatusConfigs());
        $I->assertCount(count(DiseaseConfigData::$dataArray), $gameConfig->getDiseaseConfig());
        $I->assertCount(count(EquipmentConfigData::$dataArray), $gameConfig->getEquipmentsConfig());
        $I->assertCount(count(HunterConfigData::$dataArray), $gameConfig->getHunterConfigs());
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $this->gameConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, GameConfig::class, [
            'name' => 'default',
        ]);
    }
}
