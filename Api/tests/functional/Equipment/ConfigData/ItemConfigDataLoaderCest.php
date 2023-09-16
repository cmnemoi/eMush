<?php

namespace Mush\Tests\functional\Equipment\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Equipment\ConfigData\EquipmentConfigData;
use Mush\Equipment\ConfigData\ItemConfigDataLoader;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Tests\FunctionalTester;

class ItemConfigDataLoaderCest extends EquipmentConfigDataLoaderCest
{
    private ItemConfigDataLoader $itemConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->itemConfigDataLoader = $I->grabService(ItemConfigDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->itemConfigDataLoader->loadConfigsData();

        foreach (EquipmentConfigData::$dataArray as $itemConfigData) {
            if ($itemConfigData['type'] !== 'item_config') {
                continue;
            }
            $itemConfigData = $this->dropFields($itemConfigData);
            $I->seeInRepository(ItemConfig::class, $itemConfigData);
        }

        $I->seeNumRecords($this->getNumberOfItemConfigs(), ItemConfig::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $config = [
            'name' => 'quadrimetric_compass_default',
            'equipmentName' => 'quadrimetric_compass',
            'isBreakable' => false,
            'isFireDestroyable' => false,
            'isFireBreakable' => false,
            'dismountedProducts' => [],
            'isPersonal' => false,
            'isStackable' => true,
        ];

        $config = $this->dropFields($config);

        $this->itemConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, ItemConfig::class, $config);
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
            || $key === 'isPersonal'
            || $key === 'isStackable';
        }, ARRAY_FILTER_USE_KEY);

        return $configData;
    }

    /**
     * EquipmentConfigData::$dataArray contains all the EquipmentConfigData, including the EquipmentConfig
     * so this method returns the number of ItemConfigs in the array.
     */
    private function getNumberOfItemConfigs(): int
    {
        $configs = new ArrayCollection(EquipmentConfigData::$dataArray);
        $itemConfigs = $configs->filter(function ($config) {
            return $config['type'] === 'item_config';
        });

        return $itemConfigs->count();
    }
}
