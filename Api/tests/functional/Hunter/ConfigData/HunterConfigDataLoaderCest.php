<?php

namespace Mush\Tests\functional\Hunter\ConfigData;

use App\Tests\FunctionalTester;
use Mush\Hunter\ConfigData\HunterConfigData;
use Mush\Hunter\ConfigData\HunterConfigDataLoader;
use Mush\Hunter\Entity\HunterConfig;

class HunterConfigDataLoaderCest
{
    private HunterConfigDataLoader $hunterConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->hunterConfigDataLoader = $I->grabService(HunterConfigDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->hunterConfigDataLoader->loadConfigsData();

        foreach (HunterConfigData::$dataArray as $hunterConfigData) {
            $I->seeInRepository(HunterConfig::class, $hunterConfigData);
        }

        $I->seeNumRecords(count(HunterConfigData::$dataArray), HunterConfig::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $config = HunterConfigData::$dataArray[0];

        $this->hunterConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, HunterConfig::class, $config);
    }
}
