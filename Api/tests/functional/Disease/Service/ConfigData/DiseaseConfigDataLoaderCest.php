<?php

namespace Mush\Tests\functional\Disease\Service\ConfigData;

use App\Tests\FunctionalTester;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Service\ConfigData\DiseaseConfigData;
use Mush\Disease\Service\ConfigData\DiseaseConfigDataLoader;
use Mush\Disease\Service\ConfigData\SymptomActivationRequirementDataLoader;
use Mush\Disease\Service\ConfigData\SymptomConfigDataLoader;
use Mush\Modifier\Service\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\Service\ConfigData\VariableEventModifierConfigDataLoader;

class DiseaseConfigDataLoaderCest
{
    // @TODO : remove SymptomConfig logic when it will be definitely deprecated

    private DiseaseConfigDataLoader $diseaseConfigDataLoader;
    private ModifierActivationRequirementDataLoader $modifierActivationRequirementDataLoader;
    private SymptomActivationRequirementDataLoader $symptomActivationRequirementDataLoader;
    private VariableEventModifierConfigDataLoader $modifierConfigDataLoader;
    private SymptomConfigDataLoader $symptomConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        // load dependencies
        $this->modifierActivationRequirementDataLoader = $I->grabService(ModifierActivationRequirementDataLoader::class);
        $this->symptomActivationRequirementDataLoader = $I->grabService(SymptomActivationRequirementDataLoader::class);
        $this->modifierConfigDataLoader = $I->grabService(VariableEventModifierConfigDataLoader::class);
        $this->symptomConfigDataLoader = $I->grabService(SymptomConfigDataLoader::class);

        $this->modifierActivationRequirementDataLoader->loadConfigsData();
        $this->symptomActivationRequirementDataLoader->loadConfigsData();
        $this->modifierConfigDataLoader->loadConfigsData();
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
        $I->haveInRepository(DiseaseConfig::class, [
            'diseaseName' => 'food_poisoning',
            'name' => 'food_poisoning_default',
            'type' => 'disease',
            'resistance' => 0,
            'delayMin' => 0,
            'delayLength' => 0,
            'diseasePointMin' => 4,
            'diseasePointLength' => 4,
        ]);

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
