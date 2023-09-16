<?php

namespace Mush\Tests\functional\Equipment\ConfigData;

use Mush\Action\ConfigData\ActionDataLoader;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\ConfigData\PatrolShipDataLoader;
use Mush\Equipment\Entity\Mechanics\PatrolShip;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Tests\FunctionalTester;

class PatrolShipDataLoaderCest
{
    private PatrolShipDataLoader $patrolShipDataLoader;
    private ActionDataLoader $actionDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->actionDataLoader = $I->grabService(ActionDataLoader::class);
        $this->actionDataLoader->loadConfigsData();

        $this->patrolShipDataLoader = $I->grabService(PatrolShipDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->patrolShipDataLoader->loadConfigsData();

        foreach (MechanicsData::$dataArray as $patrolShipData) {
            if ($patrolShipData['type'] !== 'patrol_ship') {
                continue;
            }
            $patrolShipData = $this->dropFields($patrolShipData);
            $I->seeInRepository(PatrolShip::class, $patrolShipData);
        }

        $I->seeNumRecords(count($this->getPatrolShipsFromMechanicsDataArray()), PatrolShip::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $config = [
            'name' => 'patrol_ship_patrol_ship_alpha_longane_default',
            'dockingPlace' => RoomEnum::ALPHA_BAY,
        ];

        $config = $this->dropFields($config);

        $this->patrolShipDataLoader->loadConfigsData();

        $I->seeNumRecords(1, PatrolShip::class, $config);
    }

    // Need to drop those fields because arrays attributes make the test buggy.
    private function dropFields(array $configData): array
    {
        $configData = array_filter($configData, function ($key) {
            return $key === 'name'
            || $key === 'dockingPlace';
        }, ARRAY_FILTER_USE_KEY);

        return $configData;
    }

    private function getPatrolShipsFromMechanicsDataArray(): array
    {
        return array_filter(MechanicsData::$dataArray, function ($data) {
            return $data['type'] === EquipmentMechanicEnum::PATROL_SHIP;
        });
    }
}
