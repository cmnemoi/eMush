<?php

namespace Mush\Tests\functional\Game\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Game\ConfigData\EventConfigData;
use Mush\Game\ConfigData\VariableEventConfigDataLoader;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Tests\FunctionalTester;

class VariableEventConfigDataLoaderCest
{
    private VariableEventConfigDataLoader $variableEventConfigDataLoaderCest;

    public function _before(FunctionalTester $I)
    {
        $this->variableEventConfigDataLoaderCest = $I->grabService(VariableEventConfigDataLoader::class);
    }

    public function testloadConfigsData(FunctionalTester $I)
    {
        $this->variableEventConfigDataLoaderCest->loadConfigsData();

        foreach (EventConfigData::$dataArray as $variableEventConfigData) {
            if ($variableEventConfigData['type'] !== 'variable_event_config') {
                continue;
            }

            $I->seeInRepository(VariableEventConfig::class, $this->dropFields($variableEventConfigData));
        }

        $I->seeNumRecords($this->getNumberOfVariableEventConfigs(), VariableEventConfig::class);
    }

    /** need to drop those fields
     * type
     *(type is only used to determine the class)
     * modifierActivationRequirements is a collection and can't be compared.
     */
    private function dropFields(array $configData): array
    {
        unset($configData['type']);

        return $configData;
    }

    /**
     * EventConfigData::$dataArray contains all the EventConfigsData, including the ones that are not VariableEventConfig,
     * so this method returns the number of VariableEventConfig in the array.
     */
    private function getNumberOfVariableEventConfigs(): int
    {
        $configs = new ArrayCollection(EventConfigData::$dataArray);
        $triggerEventModifierConfigs = $configs->filter(function ($config) {
            return $config['type'] === 'variable_event_config';
        });

        return $triggerEventModifierConfigs->count();
    }
}
