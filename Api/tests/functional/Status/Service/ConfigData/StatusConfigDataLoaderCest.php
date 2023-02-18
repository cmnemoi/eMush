<?php

namespace Mush\Tests\functional\Status\Service\ConfigData;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Modifier\Service\ConfigData\DirectModifierConfigDataLoader;
use Mush\Modifier\Service\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\Service\ConfigData\TriggerEventModifierConfigDataLoader;
use Mush\Modifier\Service\ConfigData\VariableEventModifierConfigDataLoader;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Service\ConfigData\StatusConfigData;
use Mush\Status\Service\ConfigData\StatusConfigDataLoader;

class StatusConfigDataLoaderCest
{
    private StatusConfigDataLoader $statusConfigDataLoader;
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

        $I->seeNumRecords($this->getNumberOfStatusConfigs(), StatusConfig::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $I->haveInRepository(StatusConfig::class, [
            'name' => 'alien_artefact_default',
            'statusName' => 'alien_artefact',
            'visibility' => 'public',
        ]);

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
        unset($configData['dischargeStrategy']);
        unset($configData['autoRemove']);
        unset($configData['modifierConfigs']);

        return $configData;
    }

    // remove ChargeStatusConfigs
    private function getNumberOfStatusConfigs(): int
    {
        $configs = new ArrayCollection(StatusConfigData::$dataArray);
        $statusConfigs = $configs->filter(function ($config) {
            return $config['type'] === 'status_config';
        });

        return $statusConfigs->count();
    }
}
