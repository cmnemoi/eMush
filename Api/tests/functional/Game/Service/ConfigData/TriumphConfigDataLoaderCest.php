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

    public function testLoadConfigData(FunctionalTester $I)
    {
        $I->haveInRepository(GameConfig::class, [
            'name' => 'default',
        ]);

        $this->triumphConfigDataLoader->loadConfigData();

        $I->seeInRepository(TriumphConfig::class, [
            'name' => TriumphEnum::ALIEN_SCIENCE,
            'triumph' => 16,
            'isAllCrew' => false,
            'team' => VisibilityEnum::PUBLIC,
        ]);
        $I->seeNumRecords(43, TriumphConfig::class);
    }

    public function testLoadConfigDataConfigAlreadyExists(FunctionalTester $I)
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

        $this->triumphConfigDataLoader->loadConfigData();

        $I->seeNumRecords(1, TriumphConfig::class, [
            'name' => TriumphEnum::ALIEN_SCIENCE,
            'triumph' => 16,
            'isAllCrew' => false,
            'team' => VisibilityEnum::PUBLIC,
        ]);
    }
}
