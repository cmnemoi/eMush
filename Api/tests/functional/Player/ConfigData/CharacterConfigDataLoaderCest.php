<?php

namespace Mush\Tests\functional\Player\ConfigData;

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
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Game\ConfigData\VariableEventConfigDataLoader;
use Mush\Modifier\ConfigData\DirectModifierConfigDataLoader;
use Mush\Modifier\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\ConfigData\TriggerEventModifierConfigDataLoader;
use Mush\Modifier\ConfigData\VariableEventModifierConfigDataLoader;
use Mush\Player\ConfigData\CharacterConfigData;
use Mush\Player\ConfigData\CharacterConfigDataLoader;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Status\ConfigData\ChargeStatusConfigDataLoader;
use Mush\Status\ConfigData\StatusConfigDataLoader;
use Mush\Tests\FunctionalTester;

class CharacterConfigDataLoaderCest
{
    private CharacterConfigDataLoader $characterConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        $dependanciesLoader = [
        // actions
            $I->grabService(ActionDataLoader::class),
            // init statuses
            $I->grabService(VariableEventConfigDataLoader::class),
            $I->grabService(ModifierActivationRequirementDataLoader::class),
            $I->grabService(VariableEventModifierConfigDataLoader::class),
            $I->grabService(TriggerEventModifierConfigDataLoader::class),
            $I->grabService(DirectModifierConfigDataLoader::class),
            $I->grabService(ChargeStatusConfigDataLoader::class),
            $I->grabService(StatusConfigDataLoader::class),
            // starting items
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
            $I->grabService(ItemConfigDataLoader::class),
            // init diseases
            $I->grabService(SymptomActivationRequirementDataLoader::class),
            $I->grabService(DiseaseConfigDataLoader::class),
            $I->grabService(SymptomConfigDataLoader::class),
        ];

        // load dependencies
        /** @var ConfigDataLoader $dataLoader */
        foreach ($dependanciesLoader as $dataLoader) {
            $dataLoader->loadConfigsData();
        }

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
        $config = CharacterConfigData::$dataArray[0];

        $config = $this->dropFields($config);

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
