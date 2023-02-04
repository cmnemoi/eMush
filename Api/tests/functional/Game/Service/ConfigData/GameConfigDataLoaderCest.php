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

    public function testLoadConfigData(FunctionalTester $I)
    {
        $this->gameConfigDataLoader->loadConfigData();

        $I->seeInRepository(GameConfig::class, ['name' => 'default']);
    }

    public function testLoadConfigDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $I->have(GameConfig::class, ['name' => 'default']);

        $this->gameConfigDataLoader->loadConfigData();

        $I->seeNumRecords(1, GameConfig::class, ['name' => 'default']);
    }
}
