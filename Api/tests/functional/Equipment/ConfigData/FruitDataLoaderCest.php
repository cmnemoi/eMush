<?php

namespace Mush\Tests\functional\Equipment\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\ConfigData\ActionDataLoader;
use Mush\Equipment\ConfigData\FruitDataLoader;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Tests\FunctionalTester;

class FruitDataLoaderCest
{
    private FruitDataLoader $fruitDataLoader;
    private ActionDataLoader $actionDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->actionDataLoader = $I->grabService(ActionDataLoader::class);
        $this->actionDataLoader->loadConfigsData();

        $this->fruitDataLoader = $I->grabService(FruitDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->fruitDataLoader->loadConfigsData();

        foreach (MechanicsData::$dataArray as $fruitData) {
            if ($fruitData['type'] !== 'fruit') {
                continue;
            }
            $fruitData = $this->dropFields($fruitData);
            $I->seeInRepository(Fruit::class, $fruitData);
        }

        $I->seeNumRecords($this->getNumberOfFruits(), Fruit::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $config = [
            'name' => 'fruit_banana_default',
            'satiety' => 1,
            'isPerishable' => true,
            'plantName' => 'banana_tree',
        ];

        $config = $this->dropFields($config);

        $this->fruitDataLoader->loadConfigsData();

        $I->seeNumRecords(1, Fruit::class, $config);
    }

    /** need to drop those fields because they are not in the Fruit entity.
     */
    private function dropFields(array $configData): array
    {
        $configData = array_filter($configData, function ($key) {
            return $key === 'name'
            || $key === 'satiety'
            || $key === 'isPerishable'
            || $key === 'plantName';
        }, ARRAY_FILTER_USE_KEY);

        return $configData;
    }

    /**
     * MechanicsData::$dataArray contains all the MechanicsData, including the ones that are not Fruit,
     * so this method returns the number of Fruit in the array.
     */
    private function getNumberOfFruits(): int
    {
        $configs = new ArrayCollection(MechanicsData::$dataArray);
        $fruits = $configs->filter(function ($config) {
            return $config['type'] === 'fruit';
        });

        return $fruits->count();
    }
}
