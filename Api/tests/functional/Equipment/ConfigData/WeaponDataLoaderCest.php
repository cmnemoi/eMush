<?php

namespace Mush\Tests\functional\Equipment\ConfigData;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\ConfigData\ActionDataLoader;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\ConfigData\WeaponDataLoader;
use Mush\Equipment\Entity\Mechanics\Weapon;

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
        $I->seeNumRecords($this->getNumberOfWeapons(), Weapon::class);
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

        $I->haveInRepository(Weapon::class, $config);

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

    /**
     * MechanicsData::$dataArray contains all the MechanicsData, including the ones that are not Weapon,
     * so this method returns the number of Weapon in the array.
     */
    private function getNumberOfWeapons(): int
    {
        $configs = new ArrayCollection(MechanicsData::$dataArray);
        $weapons = $configs->filter(function ($config) {
            return $config['type'] === 'weapon';
        });

        return $weapons->count();
    }
}
