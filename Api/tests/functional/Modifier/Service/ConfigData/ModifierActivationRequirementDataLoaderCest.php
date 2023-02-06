<?php

namespace Mush\Tests\functional\Modifier\Service\ConfigData;

use App\Tests\FunctionalTester;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Service\ConfigData\ModifierActivationRequirementData;
use Mush\Modifier\Service\ConfigData\ModifierActivationRequirementDataLoader;

class ModifierActivationRequirementDataLoaderCest
{
    private ModifierActivationRequirementDataLoader $modifierActivationRequirementLoader;

    public function _before(FunctionalTester $I)
    {
        $this->modifierActivationRequirementLoader = $I->grabService(ModifierActivationRequirementDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->modifierActivationRequirementLoader->loadConfigsData();

        foreach (ModifierActivationRequirementData::$dataArray as $modifierActivationRequirementData) {
            $I->seeInRepository(ModifierActivationRequirement::class, [
                'name' => $modifierActivationRequirementData['name'],
                'activationRequirementName' => $modifierActivationRequirementData['activationRequirementName'],
                'activationRequirement' => $modifierActivationRequirementData['activationRequirement'],
                'value' => $modifierActivationRequirementData['value'],
            ]);
        }

        $I->seeNumRecords(count(ModifierActivationRequirementData::$dataArray), ModifierActivationRequirement::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $I->haveInRepository(ModifierActivationRequirement::class, ModifierActivationRequirementData::$dataArray[0]);

        $this->modifierActivationRequirementLoader->loadConfigsData();

        $I->seeNumRecords(1, ModifierActivationRequirement::class, ModifierActivationRequirementData::$dataArray[0]);
    }
}
