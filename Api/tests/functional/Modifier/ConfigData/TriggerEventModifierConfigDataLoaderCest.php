<?php

namespace Mush\Tests\functional\Modifier\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Modifier\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\ConfigData\TriggerEventModifierConfigDataLoader;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Tests\FunctionalTester;

class TriggerEventModifierConfigDataLoaderCest
{
    private TriggerEventModifierConfigDataLoader $triggerEventModifierConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->triggerEventModifierConfigDataLoader = $I->grabService(TriggerEventModifierConfigDataLoader::class);

        $requirementDataLoader = $I->grabService(ModifierActivationRequirementDataLoader::class);
        $requirementDataLoader->loadConfigsData();
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->triggerEventModifierConfigDataLoader->loadConfigsData();

        foreach (ModifierConfigData::$dataArray as $triggerEventModifierConfigData) {
            if ($triggerEventModifierConfigData['type'] !== 'trigger_event_modifier') {
                continue;
            }

            $triggerEventModifierConfigData = $this->dropFields($triggerEventModifierConfigData);
            $I->seeInRepository(TriggerEventModifierConfig::class, ['name' => $triggerEventModifierConfigData['name']]);
        }

        // $I->seeNumRecords($this->getNumberOfTriggerEventModifierConfigs(), TriggerEventModifierConfig::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $config = $this->dropFields(ModifierConfigData::$dataArray[6]);

        $this->triggerEventModifierConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, TriggerEventModifierConfig::class, ['name' => $config['name']]);
    }

    /** need to drop those fields
     * type, delta, targetVariable, mode, applyOnActionParameter, modifierActivationRequirements
     *(type is only used to determine the class, targetVariable, mode, applyOnActionParameter, appliesOn are only used by VariableEventModifierConfig)
     * modifierActivationRequirements is a collection and can't be compared.
     */
    private function dropFields(array $configData): array
    {
        unset($configData['type']);
        unset($configData['delta']);
        unset($configData['targetVariable']);
        unset($configData['mode']);
        unset($configData['applyOnActionParameter']);
        unset($configData['modifierActivationRequirements']);
        unset($configData['triggeredEvent']);
        unset($configData['tagConstraints']);

        return $configData;
    }

    /**
     * ModifierConfigData::$dataArray contains all the ModifierConfigsData, including the ones that are not TriggerEventModifierConfig,
     * so this method returns the number of TriggerEventModifierConfig in the array.
     */
    private function getNumberOfTriggerEventModifierConfigs(): int
    {
        $configs = new ArrayCollection(ModifierConfigData::$dataArray);
        $triggerEventModifierConfigs = $configs->filter(function ($config) {
            return $config['type'] === 'trigger_event_modifier';
        });

        return $triggerEventModifierConfigs->count();
    }
}
