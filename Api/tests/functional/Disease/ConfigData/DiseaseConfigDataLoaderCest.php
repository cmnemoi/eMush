<?php

namespace Mush\Tests\functional\Disease\ConfigData;

use App\Tests\FunctionalTester;
use Mush\Disease\ConfigData\DiseaseConfigData;
use Mush\Disease\ConfigData\DiseaseConfigDataLoader;
use Mush\Disease\ConfigData\SymptomActivationRequirementDataLoader;
use Mush\Disease\ConfigData\SymptomConfigDataLoader;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Game\ConfigData\VariableEventConfigDataLoader;
use Mush\Modifier\ConfigData\DirectModifierConfigDataLoader;
use Mush\Modifier\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\ConfigData\VariableEventModifierConfigDataLoader;

class DiseaseConfigDataLoaderCest
{
    // @TODO : remove SymptomConfig logic when it will be definitely deprecated

    private DiseaseConfigDataLoader $diseaseConfigDataLoader;
    private VariableEventConfigDataLoader $eventConfigDataLoader;
    private ModifierActivationRequirementDataLoader $modifierActivationRequirementDataLoader;
    private SymptomActivationRequirementDataLoader $symptomActivationRequirementDataLoader;
    private VariableEventModifierConfigDataLoader $variableEventModifierConfigDataLoader;
    private DirectModifierConfigDataLoader $directModifierConfigDataLoader;
    private SymptomConfigDataLoader $symptomConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        // load dependencies
        $this->eventConfigDataLoader = $I->grabService(VariableEventConfigDataLoader::class);
        $this->modifierActivationRequirementDataLoader = $I->grabService(ModifierActivationRequirementDataLoader::class);
        $this->symptomActivationRequirementDataLoader = $I->grabService(SymptomActivationRequirementDataLoader::class);
        $this->variableEventModifierConfigDataLoader = $I->grabService(VariableEventModifierConfigDataLoader::class);
        $this->directModifierConfigDataLoader = $I->grabService(DirectModifierConfigDataLoader::class);
        $this->symptomConfigDataLoader = $I->grabService(SymptomConfigDataLoader::class);

        $this->modifierActivationRequirementDataLoader->loadConfigsData();
        $this->eventConfigDataLoader->loadConfigsData();
        $this->symptomActivationRequirementDataLoader->loadConfigsData();
        $this->variableEventModifierConfigDataLoader->loadConfigsData();
        $this->directModifierConfigDataLoader->loadConfigsData();
        $this->symptomConfigDataLoader->loadConfigsData();

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
