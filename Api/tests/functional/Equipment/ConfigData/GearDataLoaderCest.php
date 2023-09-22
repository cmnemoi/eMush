<?php

namespace Mush\Tests\functional\Equipment\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\ConfigData\ActionDataLoader;
use Mush\Equipment\ConfigData\GearDataLoader;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Modifier\ConfigData\EventModifierConfigDataLoader;
use Mush\Modifier\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\ConfigData\TriggerEventModifierConfigDataLoader;
use Mush\Modifier\ConfigData\VariableEventModifierConfigDataLoader;
use Mush\Tests\FunctionalTester;

class GearDataLoaderCest
{
    private GearDataLoader $gearDataLoader;

    // TODO: add other TriggerEventModifierConfigs if necessary

    public function _before(FunctionalTester $I)
    {
        $actionDataLoader = $I->grabService(ActionDataLoader::class);
        $variableEventModifierConfigDataLoader = $I->grabService(VariableEventModifierConfigDataLoader::class);
        $modifierActivationRequirementDataLoader = $I->grabService(ModifierActivationRequirementDataLoader::class);
        $triggerEventModifierConfigDataLoader = $I->grabService(TriggerEventModifierConfigDataLoader::class);
        $eventModifierConfigDataLoader = $I->grabService(EventModifierConfigDataLoader::class);

        $modifierActivationRequirementDataLoader->loadConfigsData();
        $actionDataLoader->loadConfigsData();
        $triggerEventModifierConfigDataLoader->loadConfigsData();
        $variableEventModifierConfigDataLoader->loadConfigsData();
        $eventModifierConfigDataLoader->loadConfigsData();

        $this->gearDataLoader = $I->grabService(GearDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->gearDataLoader->loadConfigsData();

        foreach (MechanicsData::$dataArray as $gearData) {
            if ($gearData['type'] !== 'gear') {
                continue;
            }

            $gearData = $this->dropFields($gearData);

            $I->seeInRepository(Gear::class, $gearData);
        }

        $I->seeNumRecords($this->getNumberOfGears(), Gear::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $config = [
            'name' => 'gear_stainproof_apron_default',
        ];

        $config = $this->dropFields($config);

        $this->gearDataLoader->loadConfigsData();

        $I->seeNumRecords(1, Gear::class, $config);
    }

    /** need to drop those fields because they are not in the Gear entity.
     */
    private function dropFields(array $configData): array
    {
        $configData = array_filter($configData, function ($key) {
            return $key === 'name';
        }, ARRAY_FILTER_USE_KEY);

        return $configData;
    }

    /**
     * MechanicsData::$dataArray contains all the MechanicsData, including the ones that are not Gear,
     * so this method returns the number of Gear in the array.
     */
    private function getNumberOfGears(): int
    {
        $configs = new ArrayCollection(MechanicsData::$dataArray);
        $gears = $configs->filter(function ($config) {
            return $config['type'] === 'gear';
        });

        return $gears->count();
    }
}
