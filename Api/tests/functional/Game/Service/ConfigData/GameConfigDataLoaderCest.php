<?php

namespace Mush\Tests\functional\Game\Service\ConfigData;

use App\Tests\FunctionalTester;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\ConfigData\GameConfigDataLoader;

class GameConfigDataLoaderCest
{
    private GameConfigDataLoader $gameConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->gameConfigDataLoader = $I->grabService(GameConfigDataLoader::class);
    }

    public function testloadConfigsData(FunctionalTester $I)
    {
        $this->gameConfigDataLoader->loadConfigsData();

        $I->seeInRepository(GameConfig::class, ['name' => 'default']);
    }

    public function testloadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $I->have(GameConfig::class, ['name' => 'default']);

        $this->gameConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, GameConfig::class, ['name' => 'default']);
    }
}
