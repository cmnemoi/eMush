<?php

namespace Mush\Tests\functional\Modifier\Service\ConfigData;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Service\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\Service\ConfigData\ModifierConfigData;
use Mush\Modifier\Service\ConfigData\VariableEventModifierConfigDataLoader;

class VariableEventModifierConfigDataLoaderCest
{
    private VariableEventModifierConfigDataLoader $variableEventModifierConfigDataLoader;
    private ModifierActivationRequirementDataLoader $modifierActivationRequirementDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->modifierActivationRequirementDataLoader = $I->grabService(ModifierActivationRequirementDataLoader::class);
        $this->modifierActivationRequirementDataLoader->loadConfigsData();

        $this->variableEventModifierConfigDataLoader = $I->grabService(VariableEventModifierConfigDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->variableEventModifierConfigDataLoader->loadConfigsData();

        foreach (ModifierConfigData::$dataArray as $variableEventModifierConfigData) {
            if ($variableEventModifierConfigData['type'] !== 'variable_event_modifier') {
                continue;
            }
            $variableEventModifierConfigData = $this->dropFields($variableEventModifierConfigData);
            $I->seeInRepository(VariableEventModifierConfig::class, $variableEventModifierConfigData);
        }

        $I->seeNumRecords($this->getNumberOfVariableEventModifierConfigs(), VariableEventModifierConfig::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $config = $this->dropFields(ModifierConfigData::$dataArray[0]);

        $I->haveInRepository(VariableEventModifierConfig::class, $config);

        $this->variableEventModifierConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, VariableEventModifierConfig::class, $config);
    }

    /** need to drop those fields
     * type, triggeredEvent, visibility are not in the entity
     *(type is only used to determine the class, triggeredEvent and visibility are on the TriggeredEventModifierConfig entity)
     * modifierActivationRequirements is a collection and can't be compared.
     */
    private function dropFields(array $configData): array
    {
        unset($configData['type']);
        unset($configData['triggeredEvent']);
        unset($configData['visibility']);
        unset($configData['modifierActivationRequirements']);

        return $configData;
    }

    /**
     * ModifierConfigData::$dataArray contains all the ModifierConfigsData, including the ones that are not VariableEventModifierConfig,
     * so this method returns the number of VariableEventModifierConfig in the array.
     */
    private function getNumberOfVariableEventModifierConfigs(): int
    {
        $configs = new ArrayCollection(ModifierConfigData::$dataArray);
        $variableEventModifierConfigs = $configs->filter(function ($config) {
            return $config['type'] === 'variable_event_modifier';
        });

        return $variableEventModifierConfigs->count();
    }
}
