<?php

namespace Mush\Tests\functional\Modifier\ConfigData;

use Mush\Modifier\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\ConfigData\VariableEventModifierConfigDataLoader;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Tests\FunctionalTester;

class VariableEventModifierConfigDataLoaderCest
{
    private VariableEventModifierConfigDataLoader $variableEventModifierConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        $requirementDataLoader = $I->grabService(ModifierActivationRequirementDataLoader::class);
        $requirementDataLoader->loadConfigsData();

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

        // $I->seeNumRecords($this->getNumberOfVariableEventModifierConfigs(), VariableEventModifierConfig::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $config = $this->dropFields(ModifierConfigData::$dataArray[0]);

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
        unset($configData['replaceEvent']);
        unset($configData['visibility']);
        unset($configData['modifierActivationRequirements']);
        unset($configData['tagConstraints']);

        return $configData;
    }

    /**
     * ModifierConfigData::$dataArray contains all the ModifierConfigsData, including the ones that are not VariableEventModifierConfig,
     * so this method returns the number of VariableEventModifierConfig in the array.
     */
    private function getNumberOfVariableEventModifierConfigs(): int
    {
        return count(array_filter(ModifierConfigData::$dataArray, fn ($element) => $element['type'] === 'variable_event_modifier'));
    }
}
