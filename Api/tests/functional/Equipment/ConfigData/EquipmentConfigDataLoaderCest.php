<?php

namespace Mush\Tests\functional\Equipment\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\ConfigData\ActionDataLoader;
use Mush\Equipment\ConfigData\BlueprintDataLoader;
use Mush\Equipment\ConfigData\BookDataLoader;
use Mush\Equipment\ConfigData\DocumentDataLoader;
use Mush\Equipment\ConfigData\DrugDataLoader;
use Mush\Equipment\ConfigData\EquipmentConfigData;
use Mush\Equipment\ConfigData\EquipmentConfigDataLoader;
use Mush\Equipment\ConfigData\FruitDataLoader;
use Mush\Equipment\ConfigData\GearDataLoader;
use Mush\Equipment\ConfigData\PatrolShipDataLoader;
use Mush\Equipment\ConfigData\PlantDataLoader;
use Mush\Equipment\ConfigData\RationDataLoader;
use Mush\Equipment\ConfigData\ToolDataLoader;
use Mush\Equipment\ConfigData\WeaponDataLoader;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Game\ConfigData\VariableEventConfigDataLoader;
use Mush\Modifier\ConfigData\DirectModifierConfigDataLoader;
use Mush\Modifier\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\ConfigData\TriggerEventModifierConfigDataLoader;
use Mush\Modifier\ConfigData\VariableEventModifierConfigDataLoader;
use Mush\Status\ConfigData\ChargeStatusConfigDataLoader;
use Mush\Status\ConfigData\StatusConfigDataLoader;
use Mush\Tests\FunctionalTester;

class EquipmentConfigDataLoaderCest
{
    protected EquipmentConfigDataLoader $equipmentConfigDataLoader;
    protected ActionDataLoader $actionDataLoader;
    protected BlueprintDataLoader $blueprintDataLoader;
    protected BookDataLoader $bookDataLoader;
    protected DocumentDataLoader $documentDataLoader;
    protected DrugDataLoader $drugDataLoader;
    protected FruitDataLoader $fruitDataLoader;
    protected GearDataLoader $gearDataLoader;
    protected PlantDataLoader $plantDataLoader;
    protected RationDataLoader $rationDataLoader;
    protected ToolDataLoader $toolDataLoader;
    protected WeaponDataLoader $weaponDataLoader;
    protected PatrolShipDataLoader $patrolShipDataLoader;
    protected ChargeStatusConfigDataLoader $chargeStatusConfigDataLoader;
    protected StatusConfigDataLoader $statusConfigDataLoader;
    protected VariableEventModifierConfigDataLoader $variableEventModifierConfigDataLoader;
    protected DirectModifierConfigDataLoader $directModifierConfigDataLoader;
    protected TriggerEventModifierConfigDataLoader $triggerEventModifierConfigDataLoader;
    protected VariableEventConfigDataLoader $variableEventConfigDataLoader;
    protected ModifierActivationRequirementDataLoader $modifierActivationRequirementDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->actionDataLoader = $I->grabService(ActionDataLoader::class);

        // mechanics
        $this->modifierActivationRequirementDataLoader = $I->grabService(ModifierActivationRequirementDataLoader::class);
        $this->variableEventConfigDataLoader = $I->grabService(VariableEventConfigDataLoader::class);
        $this->variableEventModifierConfigDataLoader = $I->grabService(VariableEventModifierConfigDataLoader::class);
        $this->triggerEventModifierConfigDataLoader = $I->grabService(TriggerEventModifierConfigDataLoader::class);
        $this->directModifierConfigDataLoader = $I->grabService(DirectModifierConfigDataLoader::class);
        $this->blueprintDataLoader = $I->grabService(BlueprintDataLoader::class);
        $this->bookDataLoader = $I->grabService(BookDataLoader::class);
        $this->documentDataLoader = $I->grabService(DocumentDataLoader::class);
        $this->drugDataLoader = $I->grabService(DrugDataLoader::class);
        $this->fruitDataLoader = $I->grabService(FruitDataLoader::class);
        $this->gearDataLoader = $I->grabService(GearDataLoader::class);
        $this->plantDataLoader = $I->grabService(PlantDataLoader::class);
        $this->rationDataLoader = $I->grabService(RationDataLoader::class);
        $this->toolDataLoader = $I->grabService(ToolDataLoader::class);
        $this->weaponDataLoader = $I->grabService(WeaponDataLoader::class);
        $this->patrolShipDataLoader = $I->grabService(PatrolShipDataLoader::class);

        // init statuses
        $this->chargeStatusConfigDataLoader = $I->grabService(ChargeStatusConfigDataLoader::class);
        $this->statusConfigDataLoader = $I->grabService(StatusConfigDataLoader::class);

        // load dependencies
        $this->actionDataLoader->loadConfigsData();

        $this->modifierActivationRequirementDataLoader->loadConfigsData();
        $this->variableEventConfigDataLoader->loadConfigsData();
        $this->variableEventModifierConfigDataLoader->loadConfigsData();
        $this->triggerEventModifierConfigDataLoader->loadConfigsData();
        $this->directModifierConfigDataLoader->loadConfigsData();
        $this->blueprintDataLoader->loadConfigsData();
        $this->bookDataLoader->loadConfigsData();
        $this->documentDataLoader->loadConfigsData();
        $this->drugDataLoader->loadConfigsData();
        $this->fruitDataLoader->loadConfigsData();
        $this->gearDataLoader->loadConfigsData();
        $this->plantDataLoader->loadConfigsData();
        $this->rationDataLoader->loadConfigsData();
        $this->toolDataLoader->loadConfigsData();
        $this->weaponDataLoader->loadConfigsData();
        $this->patrolShipDataLoader->loadConfigsData();

        $this->chargeStatusConfigDataLoader->loadConfigsData();
        $this->statusConfigDataLoader->loadConfigsData();

        $this->equipmentConfigDataLoader = $I->grabService(EquipmentConfigDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->equipmentConfigDataLoader->loadConfigsData();

        foreach (EquipmentConfigData::$dataArray as $equipmentConfigData) {
            if ($equipmentConfigData['type'] !== 'equipment_config') {
                continue;
            }
            $equipmentConfigData = $this->dropFields($equipmentConfigData);
            $I->seeInRepository(EquipmentConfig::class, $equipmentConfigData);
        }

        // TODO: fix this test
        // $I->seeNumRecords($this->getNumberOfEquipmentConfigs(), EquipmentConfig::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $config = [
            'name' => 'communication_center_default',
            'equipmentName' => 'communication_center',
            'isBreakable' => true,
            'isFireDestroyable' => false,
            'isFireBreakable' => true,
            'isPersonal' => false,
        ];

        $config = $this->dropFields($config);

        $this->equipmentConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, EquipmentConfig::class, $config);
    }

    /** need to drop those fields because they are not in the EquipmentConfig entity.
     */
    private function dropFields(array $configData): array
    {
        $configData = array_filter($configData, function ($key) {
            return $key === 'name'
            || $key === 'equipmentName'
            || $key === 'isBreakable'
            || $key === 'isFireDestroyable'
            || $key === 'isFireBreakable'
            || $key === 'isPersonal';
        }, ARRAY_FILTER_USE_KEY);

        return $configData;
    }

    /**
     * EquipmentConfigData::$dataArray contains all the EquipmentConfigData, including the ItemConfig
     * so this method returns the number of EquipmentConfig in the array.
     */
    private function getNumberOfEquipmentConfigs(): int
    {
        $configs = new ArrayCollection(EquipmentConfigData::$dataArray);
        $equipmentConfigs = $configs->filter(function ($config) {
            return $config['type'] === 'equipment_config';
        });

        return $equipmentConfigs->count();
    }
}
