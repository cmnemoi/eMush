<?php

namespace Mush\Tests\functional\Disease\Service\ConfigData;

use App\Tests\FunctionalTester;
use Mush\Disease\Entity\Config\SymptomActivationRequirement;
use Mush\Disease\Service\ConfigData\SymptomActivationRequirementData;
use Mush\Disease\Service\ConfigData\SymptomActivationRequirementDataLoader;

class SymptomActivationRequirementDataLoaderCest
{
    private SymptomActivationRequirementDataLoader $symptomActivationRequirementLoader;

    public function _before(FunctionalTester $I)
    {
        $this->symptomActivationRequirementLoader = $I->grabService(SymptomActivationRequirementDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->symptomActivationRequirementLoader->loadConfigsData();

        foreach (SymptomActivationRequirementData::$dataArray as $symptomActivationRequirementData) {
            $I->seeInRepository(SymptomActivationRequirement::class, [
                'name' => $symptomActivationRequirementData['name'],
                'activationRequirementName' => $symptomActivationRequirementData['activationRequirementName'],
                'activationRequirement' => $symptomActivationRequirementData['activationRequirement'],
                'value' => $symptomActivationRequirementData['value'],
            ]);
        }

        $I->seeNumRecords(count(SymptomActivationRequirementData::$dataArray), SymptomActivationRequirement::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $I->haveInRepository(SymptomActivationRequirement::class, SymptomActivationRequirementData::$dataArray[0]);

        $this->symptomActivationRequirementLoader->loadConfigsData();

        $I->seeNumRecords(1, SymptomActivationRequirement::class, SymptomActivationRequirementData::$dataArray[0]);
    }
}
