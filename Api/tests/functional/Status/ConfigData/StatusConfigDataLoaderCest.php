<?php

namespace Mush\Tests\functional\Status\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Modifier\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\ConfigData\TriggerEventModifierConfigDataLoader;
use Mush\Modifier\ConfigData\VariableEventModifierConfigDataLoader;
use Mush\Status\ConfigData\StatusConfigData;
use Mush\Status\ConfigData\StatusConfigDataLoader;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Tests\FunctionalTester;

class StatusConfigDataLoaderCest
{
    private StatusConfigDataLoader $statusConfigDataLoader;
    private ModifierActivationRequirementDataLoader $modifierActivationRequirementDataLoader;
    private TriggerEventModifierConfigDataLoader $triggerEventModifierConfigDataLoader;
    private VariableEventModifierConfigDataLoader $variableEventModifierConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        // load dependencies
        $this->modifierActivationRequirementDataLoader = $I->grabService(ModifierActivationRequirementDataLoader::class);
        $this->triggerEventModifierConfigDataLoader = $I->grabService(TriggerEventModifierConfigDataLoader::class);
        $this->variableEventModifierConfigDataLoader = $I->grabService(VariableEventModifierConfigDataLoader::class);

        $this->modifierActivationRequirementDataLoader->loadConfigsData();
        $this->triggerEventModifierConfigDataLoader->loadConfigsData();
        $this->variableEventModifierConfigDataLoader->loadConfigsData();

        $this->statusConfigDataLoader = $I->grabService(StatusConfigDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->statusConfigDataLoader->loadConfigsData();

        foreach (StatusConfigData::$dataArray as $statusConfigData) {
            if ($statusConfigData['type'] !== 'status_config') {
                continue;
            }
            $statusConfigData = $this->dropFields($statusConfigData);

            $I->seeInRepository(StatusConfig::class, $statusConfigData);
        }
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $this->statusConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, StatusConfig::class, [
            'name' => 'alien_artefact_default',
            'statusName' => 'alien_artefact',
            'visibility' => 'public',
        ]);
    }

    // remove ChargeStatusConfigs fields
    // modifierConfigs is removed because of a bug in Codeception
    private function dropFields(array $configData): array
    {
        unset($configData['type']);
        unset($configData['chargeVisibility']);
        unset($configData['chargeStrategy']);
        unset($configData['maxCharge']);
        unset($configData['startCharge']);
        unset($configData['dischargeStrategies']);
        unset($configData['autoRemove']);
        unset($configData['modifierConfigs']);

        return $configData;
    }

    // remove ChargeStatusConfigs
    private function getNumberOfStatusConfigs(): int
    {
        // TODO: fix me
        return (new ArrayCollection(StatusConfigData::$dataArray))->count();
    }
}
