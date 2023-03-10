<?php

namespace Mush\Tests\functional\Modifier\ConfigData;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Modifier\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\ConfigData\TriggerEventModifierConfigDataLoader;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;

class TriggerEventModifierConfigDataLoaderCest
{
    private TriggerEventModifierConfigDataLoader $triggerEventModifierConfigDataLoader;
    private ModifierActivationRequirementDataLoader $modifierActivationRequirementDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->modifierActivationRequirementDataLoader = $I->grabService(ModifierActivationRequirementDataLoader::class);
        $this->modifierActivationRequirementDataLoader->loadConfigsData();

        $this->triggerEventModifierConfigDataLoader = $I->grabService(TriggerEventModifierConfigDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->triggerEventModifierConfigDataLoader->loadConfigsData();

        foreach (ModifierConfigData::$dataArray as $triggerEventModifierConfigData) {
            if ($triggerEventModifierConfigData['type'] !== 'trigger_event_modifier') {
                continue;
            }
            $triggerEventModifierConfigData = $this->dropFields($triggerEventModifierConfigData);
            $I->seeInRepository(TriggerEventModifierConfig::class, $triggerEventModifierConfigData);
        }

        $I->seeNumRecords($this->getNumberOfTriggerEventModifierConfigs(), TriggerEventModifierConfig::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        // TODO: replace by an actual config when they are implemented
        $dummyConfig = [
            'name' => 'dummy',
            'modifierName' => null,
            'targetEvent' => 'move',
            'modifierRange' => 'player',
            'type' => 'trigger_event_modifier',
            'visibility' => 'public',
            'delta' => null,
            'targetVariable' => null,
            'mode' => null,
            'applyOnActionParameter' => 'value',
        ];

        $dummyConfig = $this->dropFields($dummyConfig);

        $I->haveInRepository(TriggerEventModifierConfig::class, $dummyConfig);

        $this->triggerEventModifierConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, TriggerEventModifierConfig::class, $dummyConfig);
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
        unset($configData['appliesOn']);
        unset($configData['modifierActivationRequirements']);

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
