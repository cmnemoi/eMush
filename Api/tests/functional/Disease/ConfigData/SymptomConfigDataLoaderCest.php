<?php

namespace Mush\Tests\functional\Disease\ConfigData;

use Mush\Disease\ConfigData\SymptomActivationRequirementDataLoader;
use Mush\Disease\ConfigData\SymptomConfigData;
use Mush\Disease\ConfigData\SymptomConfigDataLoader;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Tests\FunctionalTester;

class SymptomConfigDataLoaderCest
{
    private SymptomConfigDataLoader $symptomConfigDataLoader;
    private SymptomActivationRequirementDataLoader $symptomActivationRequirementDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->symptomActivationRequirementDataLoader = $I->grabService(SymptomActivationRequirementDataLoader::class);
        $this->symptomActivationRequirementDataLoader->loadConfigsData();

        $this->symptomConfigDataLoader = $I->grabService(SymptomConfigDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->symptomConfigDataLoader->loadConfigsData();

        foreach (SymptomConfigData::$dataArray as $symptomConfigData) {
            // this can't be compared
            unset($symptomConfigData['symptomActivationRequirements']);

            $I->seeInRepository(SymptomConfig::class, $symptomConfigData);
        }

        $I->seeNumRecords(count(SymptomConfigData::$dataArray), SymptomConfig::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $this->symptomConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, SymptomConfig::class, [
            'name' => 'biting_ON_new_cycle_default',
            'symptomName' => 'biting',
            'trigger' => 'new_cycle',
            'visibility' => 'public',
        ]);
    }
}
