<?php

namespace Mush\Tests\functional\Equipment\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\ConfigData\ActionDataLoader;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\ConfigData\RationDataLoader;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Tests\FunctionalTester;

class RationDataLoaderCest
{
    private RationDataLoader $rationDataLoader;
    private ActionDataLoader $actionDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->actionDataLoader = $I->grabService(ActionDataLoader::class);
        $this->actionDataLoader->loadConfigsData();

        $this->rationDataLoader = $I->grabService(RationDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->rationDataLoader->loadConfigsData();

        foreach (MechanicsData::$dataArray as $rationData) {
            if ($rationData['type'] !== 'ration') {
                continue;
            }
            $rationData = $this->dropFields($rationData);
            $I->seeInRepository(Ration::class, $rationData);
        }

        // TODO: fix this test
        // $I->seeNumRecords($this->getNumberOfRations(), Ration::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $config = [
            'name' => 'ration_standard_ration_default',
            'satiety' => 4,
            'isPerishable' => false,
        ];

        $config = $this->dropFields($config);

        // $I->haveInRepository(Ration::class, $config);

        $this->rationDataLoader->loadConfigsData();

        $I->seeNumRecords(1, Ration::class, $config);
    }

    /** need to drop those fields because they are not in the Ration entity.
     */
    private function dropFields(array $configData): array
    {
        $configData = array_filter($configData, function ($key) {
            return $key === 'name'
            || $key === 'satiety'
            || $key === 'isPerishable';
        }, ARRAY_FILTER_USE_KEY);

        return $configData;
    }

    /**
     * MechanicsData::$dataArray contains all the MechanicsData, including the ones that are not Ration,
     * so this method returns the number of Ration in the array.
     */
    private function getNumberOfRations(): int
    {
        $configs = new ArrayCollection(MechanicsData::$dataArray);
        $rations = $configs->filter(function ($config) {
            return $config['type'] === 'ration';
        });

        return $rations->count();
    }
}
