<?php

namespace Mush\Tests\functional\Equipment\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\ConfigData\ActionDataLoader;
use Mush\Equipment\ConfigData\DrugDataLoader;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\Entity\Mechanics\Drug;
use Mush\Tests\FunctionalTester;

class DrugDataLoaderCest
{
    private DrugDataLoader $drugDataLoader;
    private ActionDataLoader $actionDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->actionDataLoader = $I->grabService(ActionDataLoader::class);
        $this->actionDataLoader->loadConfigsData();

        $this->drugDataLoader = $I->grabService(DrugDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->drugDataLoader->loadConfigsData();

        foreach (MechanicsData::$dataArray as $drugData) {
            if ($drugData['type'] !== 'drug') {
                continue;
            }
            $drugData = $this->dropFields($drugData);
            $I->seeInRepository(Drug::class, $drugData);
        }

        $I->seeNumRecords($this->getNumberOfDrugs(), Drug::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $config = [
            'name' => 'drug_default',
            'satiety' => null,
            'isPerishable' => false,
        ];

        $config = $this->dropFields($config);

        $this->drugDataLoader->loadConfigsData();

        $I->seeNumRecords(1, Drug::class, $config);
    }

    /** need to drop those fields because they are not in the Drug entity.
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
     * MechanicsData::$dataArray contains all the MechanicsData, including the ones that are not Drug,
     * so this method returns the number of Drug in the array.
     */
    private function getNumberOfDrugs(): int
    {
        $configs = new ArrayCollection(MechanicsData::$dataArray);
        $drugs = $configs->filter(function ($config) {
            return $config['type'] === 'drug';
        });

        return $drugs->count();
    }
}
