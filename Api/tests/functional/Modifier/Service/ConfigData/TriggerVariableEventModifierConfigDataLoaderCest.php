<?php

namespace Mush\Tests\functional\Modifier\Service\ConfigData;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Modifier\Entity\Config\TriggerVariableEventModifierConfig;
use Mush\Modifier\Service\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\Service\ConfigData\ModifierConfigData;
use Mush\Modifier\Service\ConfigData\TriggerVariableEventModifierConfigDataLoader;

class TriggerVariableEventModifierConfigDataLoaderCest
{
    private TriggerVariableEventModifierConfigDataLoader $triggerVariableEventModifierConfigDataLoader;
    private ModifierActivationRequirementDataLoader $modifierActivationRequirementDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->modifierActivationRequirementDataLoader = $I->grabService(ModifierActivationRequirementDataLoader::class);
        $this->modifierActivationRequirementDataLoader->loadConfigsData();

        $this->triggerVariableEventModifierConfigDataLoader = $I->grabService(TriggerVariableEventModifierConfigDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->triggerVariableEventModifierConfigDataLoader->loadConfigsData();

        foreach (ModifierConfigData::$dataArray as $triggerVariableEventModifierConfigData) {
            if ($triggerVariableEventModifierConfigData['type'] !== 'trigger_event_variable_event_modifier') {
                continue;
            }
            $triggerVariableEventModifierConfigData = $this->dropFields($triggerVariableEventModifierConfigData);
            $I->seeInRepository(TriggerVariableEventModifierConfig::class, $triggerVariableEventModifierConfigData);
        }

        $I->seeNumRecords($this->getNumberOfTriggerVariableEventModifierConfigs(), TriggerVariableEventModifierConfig::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        // TODO: replace by an actual config when they are implemented
        $dummyConfig = [
            'name' => 'dummy',
            'modifierName' => null,
            'targetEvent' => 'move',
            'applyOnActionParameter' => null,
            'modifierHolderClass' => 'player',
            'type' => 'trigger_variable_event_modifier',
            'triggeredEvent' => 'vomiting',
            'visibility' => 'public',
            'delta' => -1,
            'targetVariable' => 'action_point',
            'mode' => null,
            'applyOnActionParameter' => 'value',
        ];

        $dummyConfig = $this->dropFields($dummyConfig);

        $I->haveInRepository(TriggerVariableEventModifierConfig::class, $dummyConfig);

        $this->triggerVariableEventModifierConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, TriggerVariableEventModifierConfig::class, $dummyConfig);
    }

    /** need to drop those fields
     * type, mode, applyOnActionParameter, modifierActivationRequirements
     *(type is only used to determine the class, mode, applyOnActionParameter, appliesOn are only used by VariableEventModifierConfig)
     * modifierActivationRequirements is a collection and can't be compared.
     */
    private function dropFields(array $configData): array
    {
        unset($configData['type']);
        unset($configData['mode']);
        unset($configData['applyOnActionParameter']);
        unset($configData['appliesOn']);
        unset($configData['modifierActivationRequirements']);

        return $configData;
    }

    /**
     * ModifierConfigData::$dataArray contains all the ModifierConfigsData, including the ones that are not TriggerVariableEventModifierConfig,
     * so this method returns the number of TriggerVariableEventModifierConfig in the array.
     */
    private function getNumberOfTriggerVariableEventModifierConfigs(): int
    {
        $configs = new ArrayCollection(ModifierConfigData::$dataArray);
        $triggerVariableEventModifierConfigs = $configs->filter(function ($config) {
            return $config['type'] === 'trigger_event_modifier';
        });

        return $triggerVariableEventModifierConfigs->count();
    }
}
