<?php

namespace Mush\Tests\functional\Equipment\ConfigData;

use Mush\Action\ConfigData\ActionDataLoader;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\ConfigData\WeaponDataLoader;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Tests\FunctionalTester;

class WeaponDataLoaderCest
{
    private WeaponDataLoader $weaponDataLoader;
    private ActionDataLoader $actionDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->actionDataLoader = $I->grabService(ActionDataLoader::class);
        $this->actionDataLoader->loadConfigsData();

        $this->weaponDataLoader = $I->grabService(WeaponDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->weaponDataLoader->loadConfigsData();

        foreach (MechanicsData::$dataArray as $weaponData) {
            if ($weaponData['type'] !== 'weapon') {
                continue;
            }
            $weaponData = $this->dropFields($weaponData);
            $I->seeInRepository(Weapon::class, $weaponData);
        }

        // TODO: find a way to grab only weapons
        $I->seeNumRecords(10, Weapon::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $config = [
            'name' => 'weapon_blaster_default',
            'baseAccuracy' => 50,
            'expeditionBonus' => 1,
            'criticalSuccessRate' => 5,
            'criticalFailRate' => 1,
            'oneShotRate' => 1,
        ];

        $config = $this->dropFields($config);

        $this->weaponDataLoader->loadConfigsData();

        $I->seeNumRecords(1, Weapon::class, $config);
    }

    /** need to drop those fields because they are not in the Weapon entity.
     */
    private function dropFields(array $configData): array
    {
        $configData = array_filter($configData, function ($key) {
            return $key === 'name'
            || $key === 'baseAccuracy'
            || $key === 'expeditionBonus'
            || $key === 'criticalSuccessRate'
            || $key === 'criticalFailRate'
            || $key === 'oneShotRate';
        }, ARRAY_FILTER_USE_KEY);

        return $configData;
    }
}
