<?php

namespace Mush\Tests\functional\Status\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Modifier\ConfigData\EventModifierConfigDataLoader;
use Mush\Modifier\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\ConfigData\TriggerEventModifierConfigDataLoader;
use Mush\Modifier\ConfigData\VariableEventModifierConfigDataLoader;
use Mush\Status\ConfigData\ChargeStatusConfigDataLoader;
use Mush\Status\ConfigData\StatusConfigData;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Tests\FunctionalTester;

class ChargeStatusConfigDataLoaderCest
{
    private ChargeStatusConfigDataLoader $chargeStatusConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        // load dependencies
        $modifierActivationRequirementDataLoader = $I->grabService(ModifierActivationRequirementDataLoader::class);
        $triggerEventModifierConfigDataLoader = $I->grabService(TriggerEventModifierConfigDataLoader::class);
        $eventModifierConfigDataLoader = $I->grabService(EventModifierConfigDataLoader::class);
        $variableEventModifierConfigDataLoader = $I->grabService(VariableEventModifierConfigDataLoader::class);

        $modifierActivationRequirementDataLoader->loadConfigsData();
        $triggerEventModifierConfigDataLoader->loadConfigsData();
        $variableEventModifierConfigDataLoader->loadConfigsData();
        $eventModifierConfigDataLoader->loadConfigsData();

        $this->chargeStatusConfigDataLoader = $I->grabService(ChargeStatusConfigDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->chargeStatusConfigDataLoader->loadConfigsData();

        foreach (StatusConfigData::$dataArray as $chargeStatusConfigData) {
            if ($chargeStatusConfigData['type'] !== 'charge_status_config') {
                continue;
            }

            $chargeStatusConfigData = $this->dropFields($chargeStatusConfigData);

            $I->seeInRepository(ChargeStatusConfig::class, $chargeStatusConfigData);
        }
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $this->chargeStatusConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, ChargeStatusConfig::class, [
            'name' => 'electric_charges_microwave_default',
            'statusName' => 'electric_charges',
            'visibility' => 'public',
            'chargeVisibility' => 'public',
            'chargeStrategy' => 'cycle_increment',
            'maxCharge' => 4,
            'startCharge' => 1,
            'autoRemove' => false,
        ]);
    }

    // remove fields
    // type is removed because not in the entity
    // modifierConfigs is removed because of a bug in Codeception
    private function dropFields(array $configData): array
    {
        unset($configData['type']);
        unset($configData['modifierConfigs']);
        unset($configData['dischargeStrategies']);

        return $configData;
    }

    // remove ChargeStatusConfigs
    private function getNumberOfStatusConfigs(): int
    {
        $configs = new ArrayCollection(StatusConfigData::$dataArray);
        $chargeStatusConfigs = $configs->filter(function ($config) {
            return $config['type'] === 'charge_status_config';
        });

        return $chargeStatusConfigs->count();
    }
}
