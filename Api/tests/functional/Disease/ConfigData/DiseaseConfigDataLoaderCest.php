<?php

namespace Mush\Tests\functional\Disease\ConfigData;

use Mush\Disease\ConfigData\DiseaseConfigData;
use Mush\Disease\ConfigData\DiseaseConfigDataLoader;
use Mush\Disease\ConfigData\SymptomActivationRequirementDataLoader;
use Mush\Disease\ConfigData\SymptomConfigDataLoader;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Game\ConfigData\VariableEventConfigDataLoader;
use Mush\Modifier\ConfigData\DirectModifierConfigDataLoader;
use Mush\Modifier\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\ConfigData\TriggerEventModifierConfigDataLoader;
use Mush\Modifier\ConfigData\VariableEventModifierConfigDataLoader;
use Mush\Tests\FunctionalTester;

class DiseaseConfigDataLoaderCest
{
    // @TODO : remove SymptomConfig logic when it will be definitely deprecated

    private DiseaseConfigDataLoader $diseaseConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        // load dependencies
        /** @var VariableEventModifierConfigDataLoader $eventConfigDataLoader */
        $eventConfigDataLoader = $I->grabService(VariableEventConfigDataLoader::class);
        /** @var ModifierActivationRequirementDataLoader $modifierActivationRequirementDataLoader */
        $modifierActivationRequirementDataLoader = $I->grabService(ModifierActivationRequirementDataLoader::class);
        /** @var SymptomActivationRequirementDataLoader $symptomActivationRequirementDataLoader */
        $symptomActivationRequirementDataLoader = $I->grabService(SymptomActivationRequirementDataLoader::class);
        /** @var VariableEventModifierConfigDataLoader $variableEventModifierConfigDataLoader */
        $triggerEventModifierConfigDataLoader = $I->grabService(VariableEventModifierConfigDataLoader::class);
        /** @var TriggerEventModifierConfigDataLoader $variableEventModifierConfigDataLoader */
        $variableEventModifierConfigDataLoader = $I->grabService(TriggerEventModifierConfigDataLoader::class);
        /** @var DirectModifierConfigDataLoader $directModifierConfigDataLoader */
        $directModifierConfigDataLoader = $I->grabService(DirectModifierConfigDataLoader::class);
        /** @var SymptomConfigDataLoader $symptomConfigDataLoader */
        $symptomConfigDataLoader = $I->grabService(SymptomConfigDataLoader::class);

        $modifierActivationRequirementDataLoader->loadConfigsData();
        $eventConfigDataLoader->loadConfigsData();
        $symptomActivationRequirementDataLoader->loadConfigsData();
        $variableEventModifierConfigDataLoader->loadConfigsData();
        $triggerEventModifierConfigDataLoader->loadConfigsData();
        $directModifierConfigDataLoader->loadConfigsData();
        $symptomConfigDataLoader->loadConfigsData();

        $this->diseaseConfigDataLoader = $I->grabService(DiseaseConfigDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->diseaseConfigDataLoader->loadConfigsData();

        foreach (DiseaseConfigData::$dataArray as $diseaseConfigData) {
            $diseaseConfigData = $this->dropArrayFields($diseaseConfigData);

            $I->seeInRepository(DiseaseConfig::class, $diseaseConfigData);
        }

        $I->seeNumRecords(count(DiseaseConfigData::$dataArray), DiseaseConfig::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $this->diseaseConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, DiseaseConfig::class, [
            'diseaseName' => 'food_poisoning',
            'name' => 'food_poisoning_default',
            'type' => 'disease',
            'resistance' => 0,
            'delayMin' => 0,
            'delayLength' => 0,
            'diseasePointMin' => 4,
            'diseasePointLength' => 4,
        ]);
    }

    // can't compare arrays with Codeception
    private function dropArrayFields(array $configData): array
    {
        unset($configData['override']);
        unset($configData['modifierConfigs']);
        unset($configData['symptomConfigs']);

        return $configData;
    }
}
