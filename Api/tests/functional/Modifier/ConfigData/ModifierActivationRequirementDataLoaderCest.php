<?php

namespace Mush\Tests\functional\Modifier\ConfigData;

use Mush\Modifier\ConfigData\ModifierActivationRequirementData;
use Mush\Modifier\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Tests\FunctionalTester;

class ModifierActivationRequirementDataLoaderCest
{
    private ModifierActivationRequirementDataLoader $modifierActivationRequirementDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->modifierActivationRequirementDataLoader = $I->grabService(ModifierActivationRequirementDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->modifierActivationRequirementDataLoader->loadConfigsData();

        foreach (ModifierActivationRequirementData::$dataArray as $modifierActivationRequirementDataData) {
            $I->seeInRepository(ModifierActivationRequirement::class, [
                'name' => $modifierActivationRequirementDataData['name'],
                'activationRequirementName' => $modifierActivationRequirementDataData['activationRequirementName'],
                'activationRequirement' => $modifierActivationRequirementDataData['activationRequirement'],
                'value' => $modifierActivationRequirementDataData['value'],
            ]);
        }

        // $I->seeNumRecords(count(ModifierActivationRequirementData::$dataArray), ModifierActivationRequirement::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $this->modifierActivationRequirementDataLoader->loadConfigsData();

        $I->seeNumRecords(1, ModifierActivationRequirement::class, ModifierActivationRequirementData::$dataArray[0]);
    }
}
