<?php

namespace Mush\Tests\functional\Triumph\Service\ConfigData;

use App\Tests\FunctionalTester;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\TriumphConfig;
use Mush\Game\Enum\TriumphEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\ConfigData\TriumphConfigDataLoader;

class TriumphConfigDataLoaderCest
{
    private TriumphConfigDataLoader $triumphConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->triumphConfigDataLoader = $I->grabService(TriumphConfigDataLoader::class);
    }

    public function testloadConfigsData(FunctionalTester $I)
    {
        $I->haveInRepository(GameConfig::class, [
            'name' => 'default',
        ]);
        $defaultGameConfig = $I->grabEntityFromRepository(GameConfig::class, [
            'name' => 'default',
        ]);

        $this->triumphConfigDataLoader->loadConfigsData();

        $I->seeInRepository(TriumphConfig::class, [
            'name' => TriumphEnum::ALIEN_SCIENCE,
            'triumph' => 16,
            'isAllCrew' => false,
            'team' => VisibilityEnum::PUBLIC,
        ]);

        // check that we've loaded all the triumph configs
        $I->seeNumRecords(43, TriumphConfig::class);
        // check that we've associated successfully the triumph configs with the default game config
        $I->assertCount(43, $defaultGameConfig->getTriumphConfig());
    }

    public function testloadConfigsDataConfigAlreadyExists(FunctionalTester $I)
    {
        $I->haveInRepository(GameConfig::class, [
            'name' => 'default',
        ]);

        $I->haveInRepository(TriumphConfig::class, [
            'name' => TriumphEnum::ALIEN_SCIENCE,
            'triumph' => 16,
            'isAllCrew' => false,
            'team' => VisibilityEnum::PUBLIC,
        ]);

        $this->triumphConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, TriumphConfig::class, [
            'name' => TriumphEnum::ALIEN_SCIENCE,
            'triumph' => 16,
            'isAllCrew' => false,
            'team' => VisibilityEnum::PUBLIC,
        ]);
    }
}
