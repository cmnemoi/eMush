<?php

namespace Mush\Tests\functional\Disease\ConfigData;

use Mush\Disease\ConfigData\ConsumableDiseaseConfigData;
use Mush\Disease\ConfigData\ConsumableDiseaseConfigDataLoader;
use Mush\Disease\Entity\Config\ConsumableDiseaseConfig;
use Mush\Tests\FunctionalTester;

class ConsumableDiseaseConfigDataLoaderCest
{
    private ConsumableDiseaseConfigDataLoader $diseaseCauseConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->diseaseCauseConfigDataLoader = $I->grabService(ConsumableDiseaseConfigDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->diseaseCauseConfigDataLoader->loadConfigsData();

        foreach (ConsumableDiseaseConfigData::$dataArray as $diseaseCauseConfigData) {
            $diseaseCauseConfigData = $this->dropArrayFields($diseaseCauseConfigData);

            $I->seeInRepository(ConsumableDiseaseConfig::class, $diseaseCauseConfigData);
        }

        $I->seeNumRecords(count(ConsumableDiseaseConfigData::$dataArray), ConsumableDiseaseConfig::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $config = ConsumableDiseaseConfigData::$dataArray[0];
        $config = $this->dropArrayFields($config);

        $this->diseaseCauseConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, ConsumableDiseaseConfig::class, $config);
    }

    // can't compare arrays because of Codeception bug
    private function dropArrayFields(array $configArray)
    {
        unset($configArray['diseasesName']);
        unset($configArray['curesName']);
        unset($configArray['diseasesChances']);
        unset($configArray['curesChances']);
        unset($configArray['diseasesDelayMin']);
        unset($configArray['diseasesDelayLength']);
        unset($configArray['effectNumber']);

        return $configArray;
    }
}
