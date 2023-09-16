<?php

namespace Mush\Tests\functional\Daedalus\ConfigData;

use Mush\Daedalus\ConfigData\DaedalusConfigDataLoader;
use Mush\Daedalus\ConfigData\RandomItemPlacesDataLoader;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\RandomItemPlaces;
use Mush\Place\ConfigData\PlaceConfigDataLoader;
use Mush\Tests\FunctionalTester;

class DaedalusConfigDataLoaderCest
{
    private DaedalusConfigDataLoader $daedalusConfigDataLoader;
    private RandomItemPlacesDataLoader $randomItemPlacesDataLoader;
    private PlaceConfigDataLoader $placeConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->placeConfigDataLoader = $I->grabService(PlaceConfigDataLoader::class);
        $this->randomItemPlacesDataLoader = $I->grabService(RandomItemPlacesDataLoader::class);

        $this->daedalusConfigDataLoader = $I->grabService(DaedalusConfigDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        // load dependencies
        $this->randomItemPlacesDataLoader->loadConfigsData();
        $this->placeConfigDataLoader->loadConfigsData();

        $this->daedalusConfigDataLoader->loadConfigsData();

        // TODO: can't really check RandomItemPlaces and PlaceConfigs...

        $I->seeInRepository(DaedalusConfig::class, [
            'name' => 'default',
            'initOxygen' => 32,
            'initFuel' => 20,
            'initHull' => 100,
            'initShield' => -2,
            'maxOxygen' => 32,
            'maxFuel' => 32,
            'maxHull' => 100,
            'maxShield' => 100,
            'dailySporeNb' => 4,
            'nbMush' => 2,
            'cyclePerGameDay' => 8,
            'cycleLength' => 180,
        ]);

        $I->seeNumRecords(1, DaedalusConfig::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $this->daedalusConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, DaedalusConfig::class, [
            'name' => 'default',
            'initOxygen' => 32,
            'initFuel' => 20,
            'initHull' => 100,
            'initShield' => -2,
            'maxOxygen' => 32,
            'maxFuel' => 32,
            'maxHull' => 100,
            'maxShield' => 100,
            'dailySporeNb' => 4,
            'nbMush' => 2,
            'cyclePerGameDay' => 8,
            'cycleLength' => 180,
        ]);
    }
}
