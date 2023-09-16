<?php

namespace Mush\Tests\functional\Game\ConfigData;

use Mush\Game\ConfigData\TriumphConfigDataLoader;
use Mush\Game\Entity\TriumphConfig;
use Mush\Game\Enum\TriumphEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Tests\FunctionalTester;

class TriumphConfigDataLoaderCest
{
    private TriumphConfigDataLoader $triumphConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->triumphConfigDataLoader = $I->grabService(TriumphConfigDataLoader::class);
    }

    public function testloadConfigsData(FunctionalTester $I)
    {
        $this->triumphConfigDataLoader->loadConfigsData();

        $I->seeInRepository(TriumphConfig::class, [
            'name' => TriumphEnum::ALIEN_SCIENCE,
            'triumph' => 16,
            'isAllCrew' => false,
            'team' => VisibilityEnum::PUBLIC,
        ]);

        // check that we've loaded all the triumph configs
        $I->seeNumRecords(43, TriumphConfig::class);
    }

    public function testloadConfigsDataConfigAlreadyExists(FunctionalTester $I)
    {
        $this->triumphConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, TriumphConfig::class, [
            'name' => TriumphEnum::ALIEN_SCIENCE,
            'triumph' => 16,
            'isAllCrew' => false,
            'team' => VisibilityEnum::PUBLIC,
        ]);
    }
}
