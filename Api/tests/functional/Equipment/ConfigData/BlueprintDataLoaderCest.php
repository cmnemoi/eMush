<?php

namespace Mush\Tests\functional\Equipment\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\ConfigData\ActionDataLoader;
use Mush\Equipment\ConfigData\BlueprintDataLoader;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Tests\FunctionalTester;

class BlueprintDataLoaderCest
{
    private BlueprintDataLoader $blueprintDataLoader;
    private ActionDataLoader $actionDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->actionDataLoader = $I->grabService(ActionDataLoader::class);
        $this->actionDataLoader->loadConfigsData();

        $this->blueprintDataLoader = $I->grabService(BlueprintDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->blueprintDataLoader->loadConfigsData();

        foreach (MechanicsData::$dataArray as $blueprintData) {
            if ($blueprintData['type'] !== 'blueprint') {
                continue;
            }
            $blueprintData = $this->dropFields($blueprintData);
            $I->seeInRepository(Blueprint::class, $blueprintData);
        }

        $I->seeNumRecords($this->getNumberOfBlueprints(), Blueprint::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $config = [
            'name' => 'echolocator_blueprint_default',
            'craftedEquipmentName' => 'echolocator',
        ];

        $config = $this->dropFields($config);

        $this->blueprintDataLoader->loadConfigsData();

        $I->seeNumRecords(1, Blueprint::class, $config);
    }

    /** need to drop those fields because they are not in the Blueprint entity.
     */
    private function dropFields(array $configData): array
    {
        // drop everything instead name field
        $configData = array_filter($configData, function ($key) {
            return $key === 'name'
            || $key === 'craftedEquipmentName';
        }, ARRAY_FILTER_USE_KEY);

        return $configData;
    }

    /**
     * MechanicsData::$dataArray contains all the MechanicsData, including the ones that are not Blueprint,
     * so this method returns the number of Blueprint in the array.
     */
    private function getNumberOfBlueprints(): int
    {
        $configs = new ArrayCollection(MechanicsData::$dataArray);
        $blueprints = $configs->filter(function ($config) {
            return $config['type'] === 'blueprint';
        });

        return $blueprints->count();
    }
}
