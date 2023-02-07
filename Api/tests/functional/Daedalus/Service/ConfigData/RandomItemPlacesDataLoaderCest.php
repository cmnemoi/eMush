<?php

namespace Mush\Tests\functional\Daedalus\Service\ConfigData;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\RandomItemPlaces;
use Mush\Daedalus\Service\ConfigData\RandomItemPlacesData;
use Mush\Daedalus\Service\ConfigData\RandomItemPlacesDataLoader;

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
        $I->haveInRepository(RandomItemPlaces::class, RandomItemPlacesData::$dataArray[0]);

        $this->randomItemPlacesDataLoader->loadConfigsData();

        $I->seeNumRecords(1, RandomItemPlaces::class, [
            'name' => 'default',
        ]);
    }
}
