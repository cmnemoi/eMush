<?php

namespace Mush\Tests\functional\Equipment\ConfigData;

use App\Tests\FunctionalTester;
use Mush\Action\ConfigData\ActionDataLoader;
use Mush\Disease\ConfigData\DiseaseConfigDataLoader;
use Mush\Disease\ConfigData\SymptomActivationRequirementDataLoader;
use Mush\Disease\ConfigData\SymptomConfigDataLoader;
use Mush\Equipment\ConfigData\BlueprintDataLoader;
use Mush\Equipment\ConfigData\BookDataLoader;
use Mush\Equipment\ConfigData\DocumentDataLoader;
use Mush\Equipment\ConfigData\DrugDataLoader;
use Mush\Equipment\ConfigData\FruitDataLoader;
use Mush\Equipment\ConfigData\GearDataLoader;
use Mush\Equipment\ConfigData\ItemConfigDataLoader;
use Mush\Equipment\ConfigData\PlantDataLoader;
use Mush\Equipment\ConfigData\RationDataLoader;
use Mush\Equipment\ConfigData\ToolDataLoader;
use Mush\Equipment\ConfigData\WeaponDataLoader;
use Mush\Game\ConfigData\VariableEventConfigDataLoader;
use Mush\Modifier\ConfigData\DirectModifierConfigDataLoader;
use Mush\Modifier\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\ConfigData\VariableEventModifierConfigDataLoader;
use Mush\Player\ConfigData\CharacterConfigData;
use Mush\Player\ConfigData\CharacterConfigDataLoader;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Status\ConfigData\ChargeStatusConfigDataLoader;
use Mush\Status\ConfigData\StatusConfigDataLoader;

class CharacterConfigDataLoaderCest
{
    private CharacterConfigDataLoader $characterConfigDataLoader;

    // actions
    private ActionDataLoader $actionDataLoader;

    // init statuses
    private VariableEventConfigDataLoader $eventConfigDataLoader;
    private ModifierActivationRequirementDataLoader $modifierActivationRequirementDataLoader;
    private VariableEventModifierConfigDataLoader $variableEventModifierConfigDataLoader;
    private DirectModifierConfigDataLoader $directModifierConfigDataLoader;
    private ChargeStatusConfigDataLoader $chargeStatusConfigDataLoader;
    private StatusConfigDataLoader $statusConfigDataLoader;

    // starting items
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
    private ItemConfigDataLoader $itemConfigDataLoader;

    // init diseases
    private SymptomActivationRequirementDataLoader $symptomActivationRequirementDataLoader;
    private SymptomConfigDataLoader $symptomConfigDataLoader;
    private DiseaseConfigDataLoader $diseaseConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        // actions
        $this->actionDataLoader = $I->grabService(ActionDataLoader::class);

        // init statuses
        $this->eventConfigDataLoader = $I->grabService(VariableEventConfigDataLoader::class);
        $this->modifierActivationRequirementDataLoader = $I->grabService(ModifierActivationRequirementDataLoader::class);
        $this->variableEventModifierConfigDataLoader = $I->grabService(VariableEventModifierConfigDataLoader::class);
        $this->directModifierConfigDataLoader = $I->grabService(DirectModifierConfigDataLoader::class);
        $this->chargeStatusConfigDataLoader = $I->grabService(ChargeStatusConfigDataLoader::class);
        $this->statusConfigDataLoader = $I->grabService(StatusConfigDataLoader::class);

        // starting items
        $this->blueprintDataLoader = $I->grabService(BlueprintDataLoader::class);
        $this->bookDataLoader = $I->grabService(BookDataLoader::class);
        $this->documentDataLoader = $I->grabService(DocumentDataLoader::class);
        $this->drugDataLoader = $I->grabService(DrugDataLoader::class);
        $this->fruitDataLoader = $I->grabService(FruitDataLoader::class);
        $this->gearDataLoader = $I->grabService(GearDataLoader::class);
        $this->plantDataLoader = $I->grabService(PlantDataLoader::class);
        $this->rationDataLoader = $I->grabService(RationDataLoader::class);
        $this->toolDataLoader = $I->grabService(ToolDataLoader::class);
        $this->weaponDataLoader = $I->grabService(WeaponDataLoader::class);
        $this->itemConfigDataLoader = $I->grabService(ItemConfigDataLoader::class);

        // init diseases
        $this->symptomActivationRequirementDataLoader = $I->grabService(SymptomActivationRequirementDataLoader::class);
        $this->diseaseConfigDataLoader = $I->grabService(DiseaseConfigDataLoader::class);
        $this->symptomConfigDataLoader = $I->grabService(SymptomConfigDataLoader::class);

        // load dependencies
        $this->actionDataLoader->loadConfigsData();

        $this->eventConfigDataLoader->loadConfigsData();
        $this->modifierActivationRequirementDataLoader->loadConfigsData();
        $this->directModifierConfigDataLoader->loadConfigsData();
        $this->variableEventModifierConfigDataLoader->loadConfigsData();
        $this->chargeStatusConfigDataLoader->loadConfigsData();
        $this->statusConfigDataLoader->loadConfigsData();

        $this->blueprintDataLoader->loadConfigsData();
        $this->bookDataLoader->loadConfigsData();
        $this->documentDataLoader->loadConfigsData();
        $this->drugDataLoader->loadConfigsData();
        $this->fruitDataLoader->loadConfigsData();
        $this->gearDataLoader->loadConfigsData();
        $this->plantDataLoader->loadConfigsData();
        $this->rationDataLoader->loadConfigsData();
        $this->toolDataLoader->loadConfigsData();
        $this->weaponDataLoader->loadConfigsData();
        $this->itemConfigDataLoader->loadConfigsData();

        $this->symptomActivationRequirementDataLoader->loadConfigsData();
        $this->symptomConfigDataLoader->loadConfigsData();
        $this->diseaseConfigDataLoader->loadConfigsData();

        $this->characterConfigDataLoader = $I->grabService(CharacterConfigDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->characterConfigDataLoader->loadConfigsData();

        foreach (CharacterConfigData::$dataArray as $characterConfigData) {
            $characterConfigData = $this->dropFields($characterConfigData);
            $I->seeInRepository(CharacterConfig::class, $characterConfigData);
        }

        $I->seeNumRecords(count(CharacterConfigData::$dataArray), CharacterConfig::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $config = [
            'name' => 'andie',
            'characterName' => 'andie',
            'maxNumberPrivateChannel' => 3,
            'maxHealthPoint' => 14,
            'maxMoralPoint' => 14,
            'maxActionPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 3,
            'initHealthPoint' => 14,
            'initMoralPoint' => 14,
            'initSatiety' => 0,
            'initActionPoint' => 8,
            'initMovementPoint' => 12,
        ];

        $config = $this->dropFields($config);

        $I->haveInRepository(CharacterConfig::class, $config);

        $this->characterConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, CharacterConfig::class, $config);
    }

    // need to drop those fields because of a Codeception bug
    private function dropFields(array $configData): array
    {
        $configData = array_filter($configData, function ($key) {
            return $key === 'name'
            || $key === 'characterName'
            || $key === 'maxNumberPrivateChannel'
            || $key === 'maxHealthPoint'
            || $key === 'maxMoralPoint'
            || $key === 'maxActionPoint'
            || $key === 'maxMovementPoint'
            || $key === 'maxItemInInventory'
            || $key === 'initHealthPoint'
            || $key === 'initMoralPoint'
            || $key === 'initSatiety'
            || $key === 'initActionPoint'
            || $key === 'initMovementPoint';
        }, ARRAY_FILTER_USE_KEY);

        return $configData;
    }
}
