<?php

namespace Mush\Tests\functional\Game\ConfigData;

use Mush\Game\ConfigData\LocalizationConfigData;
use Mush\Game\ConfigData\LocalizationConfigDataLoader;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Tests\FunctionalTester;

class LocalizationConfigDataLoaderCest
{
    private LocalizationConfigDataLoader $localizationConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->localizationConfigDataLoader = $I->grabService(LocalizationConfigDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->localizationConfigDataLoader->loadConfigsData();

        foreach (LocalizationConfigData::$dataArray as $data) {
            $I->seeInRepository(LocalizationConfig::class, $data);
        }

        $I->seeNumRecords(count(LocalizationConfigData::$dataArray), LocalizationConfig::class);
    }

    public function testLoadConfigsDataConfigAlreadyExists(FunctionalTester $I)
    {
        $this->localizationConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, LocalizationConfig::class, LocalizationConfigData::$dataArray[0]);
    }
}
