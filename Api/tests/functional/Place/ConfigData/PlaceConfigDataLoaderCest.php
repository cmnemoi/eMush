<?php

namespace Mush\Tests\functional\Place\ConfigData;

use Mush\Place\ConfigData\PlaceConfigData;
use Mush\Place\ConfigData\PlaceConfigDataLoader;
use Mush\Place\Entity\PlaceConfig;
use Mush\Tests\FunctionalTester;

class PlaceConfigDataLoaderCest
{
    private PlaceConfigDataLoader $placeConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->placeConfigDataLoader = $I->grabService(PlaceConfigDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->placeConfigDataLoader->loadConfigsData();

        $I->seeInRepository(PlaceConfig::class, [
            'name' => 'bridge_default',
            'placeName' => 'bridge',
            'type' => 'room',
        ]);

        $I->seeNumRecords(count(PlaceConfigData::$dataArray), PlaceConfig::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $this->placeConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, PlaceConfig::class, [
            'name' => 'bridge_default',
            'placeName' => 'bridge',
            'type' => 'room',
        ]);
    }
}
