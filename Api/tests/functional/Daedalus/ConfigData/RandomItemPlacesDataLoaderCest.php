<?php

namespace Mush\Tests\functional\Daedalus\ConfigData;

use Mush\Daedalus\ConfigData\RandomItemPlacesDataLoader;
use Mush\Daedalus\Entity\RandomItemPlaces;
use Mush\Tests\FunctionalTester;

class RandomItemPlacesDataLoaderCest
{
    private RandomItemPlacesDataLoader $randomItemPlacesDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->randomItemPlacesDataLoader = $I->grabService(RandomItemPlacesDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->randomItemPlacesDataLoader->loadConfigsData();

        $I->seeInRepository(RandomItemPlaces::class, [
            'name' => 'default',
        ]);

        $I->seeNumRecords(1, RandomItemPlaces::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $this->randomItemPlacesDataLoader->loadConfigsData();

        $I->seeNumRecords(1, RandomItemPlaces::class, [
            'name' => 'default',
        ]);
    }
}
