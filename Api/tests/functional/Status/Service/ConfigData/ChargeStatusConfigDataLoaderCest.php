<?php

namespace Mush\Tests\functional\Status\Service\ConfigData;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Modifier\Service\ConfigData\DirectModifierConfigDataLoader;
use Mush\Modifier\Service\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\Service\ConfigData\TriggerEventModifierConfigDataLoader;
use Mush\Modifier\Service\ConfigData\VariableEventModifierConfigDataLoader;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Service\ConfigData\ChargeStatusConfigDataLoader;
use Mush\Status\Service\ConfigData\StatusConfigData;

class ChargeStatusConfigDataLoaderCest
{
    private ChargeStatusConfigDataLoader $chargeStatusConfigDataLoader;
    private ModifierActivationRequirementDataLoader $modifierActivationRequirementDataLoader;
    private TriggerEventModifierConfigDataLoader $triggerEventModifierConfigDataLoader;
    private DirectModifierConfigDataLoader $triggerVariableEventModifierConfigDataLoader;
    private VariableEventModifierConfigDataLoader $variableEventModifierConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        // load dependencies
        $this->modifierActivationRequirementDataLoader = $I->grabService(ModifierActivationRequirementDataLoader::class);
        $this->triggerEventModifierConfigDataLoader = $I->grabService(TriggerEventModifierConfigDataLoader::class);
        $this->triggerVariableEventModifierConfigDataLoader = $I->grabService(DirectModifierConfigDataLoader::class);
        $this->variableEventModifierConfigDataLoader = $I->grabService(VariableEventModifierConfigDataLoader::class);

        $this->modifierActivationRequirementDataLoader->loadConfigsData();
        $this->triggerEventModifierConfigDataLoader->loadConfigsData();
        $this->triggerVariableEventModifierConfigDataLoader->loadConfigsData();
        $this->variableEventModifierConfigDataLoader->loadConfigsData();

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

        $I->seeNumRecords($this->getNumberOfStatusConfigs(), ChargeStatusConfig::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $I->haveInRepository(ChargeStatusConfig::class, [
            'name' => 'electricCharges_old_faithful_default',
            'statusName' => 'electricCharges',
            'visibility' => 'public',
            'chargeVisibility' => 'public',
            'chargeStrategy' => 'cycle_increment',
            'maxCharge' => 12.0,
            'startCharge' => 12.0,
            'dischargeStrategy' => 'shoot',
            'autoRemove' => false,
        ]);

        $this->chargeStatusConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, ChargeStatusConfig::class, [
            'name' => 'electricCharges_old_faithful_default',
            'statusName' => 'electricCharges',
            'visibility' => 'public',
            'chargeVisibility' => 'public',
            'chargeStrategy' => 'cycle_increment',
            'maxCharge' => 12.0,
            'startCharge' => 12.0,
            'dischargeStrategy' => 'shoot',
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
