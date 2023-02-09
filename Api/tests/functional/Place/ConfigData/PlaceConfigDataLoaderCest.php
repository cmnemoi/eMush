<?php

namespace Mush\Tests\functional\Place\ConfigData;

use App\Tests\FunctionalTester;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\ConfigData\PlaceConfigData;
use Mush\Place\ConfigData\PlaceConfigDataLoader;

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

        $I->seeNumRecords(27, PlaceConfig::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $I->haveInRepository(PlaceConfig::class, PlaceConfigData::$dataArray[0]);

        $this->placeConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, PlaceConfig::class, [
            'name' => 'bridge_default',
            'placeName' => 'bridge',
            'type' => 'room',
        ]);
    }
}
