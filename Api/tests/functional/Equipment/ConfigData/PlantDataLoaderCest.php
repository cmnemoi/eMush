<?php

namespace Mush\Tests\functional\Equipment\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\ConfigData\ActionDataLoader;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\ConfigData\PlantDataLoader;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Tests\FunctionalTester;

class PlantDataLoaderCest
{
    private PlantDataLoader $plantDataLoader;
    private ActionDataLoader $actionDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->actionDataLoader = $I->grabService(ActionDataLoader::class);
        $this->actionDataLoader->loadConfigsData();

        $this->plantDataLoader = $I->grabService(PlantDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->plantDataLoader->loadConfigsData();

        foreach (MechanicsData::$dataArray as $plantData) {
            if ($plantData['type'] !== 'plant') {
                continue;
            }
            $plantData = $this->dropFields($plantData);
            $I->seeInRepository(Plant::class, $plantData);
        }

        $I->seeNumRecords($this->getNumberOfPlants(), Plant::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $config = [
            'name' => 'plant_banana_tree_default',
            'fruitName' => 'banana',
        ];

        $config = $this->dropFields($config);

        $this->plantDataLoader->loadConfigsData();

        $I->seeNumRecords(1, Plant::class, $config);
    }

    /** need to drop those fields because they are not in the Plant entity.
     */
    private function dropFields(array $configData): array
    {
        $configData = array_filter($configData, function ($key) {
            return $key === 'name'
            || $key === 'fruitName';
        }, ARRAY_FILTER_USE_KEY);

        return $configData;
    }

    /**
     * MechanicsData::$dataArray contains all the MechanicsData, including the ones that are not Plant,
     * so this method returns the number of Plant in the array.
     */
    private function getNumberOfPlants(): int
    {
        $configs = new ArrayCollection(MechanicsData::$dataArray);
        $plants = $configs->filter(function ($config) {
            return $config['type'] === 'plant';
        });

        return $plants->count();
    }
}
