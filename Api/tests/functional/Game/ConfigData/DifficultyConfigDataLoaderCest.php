<?php

namespace Mush\Tests\functional\Triumph\ConfigData;

use App\Tests\FunctionalTester;
use Mush\Game\ConfigData\DifficultyConfigDataLoader;
use Mush\Game\Entity\DifficultyConfig;

class DifficultyConfigDataLoaderCest
{
    private DifficultyConfigDataLoader $difficultyConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->difficultyConfigDataLoader = $I->grabService(DifficultyConfigDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->difficultyConfigDataLoader->loadConfigsData();

        $I->seeInRepository(DifficultyConfig::class, [
            'name' => 'default',
            'equipmentBreakRate' => 30,
            'doorBreakRate' => 40,
            'equipmentFireBreakRate' => 30,
            'startingFireRate' => 2,
            'propagatingFireRate' => 30,
            'hullFireDamageRate' => 20,
            'tremorRate' => 5,
            'electricArcRate' => 5,
            'metalPlateRate' => 5,
            'panicCrisisRate' => 5,
        ]);

        // check that we've loaded all the difficulty configs
        $I->seeNumRecords(1, DifficultyConfig::class);
    }

    public function testLoadConfigsDataConfigAlreadyExists(FunctionalTester $I)
    {
        $this->difficultyConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, DifficultyConfig::class, [
            'name' => 'default',
            'equipmentBreakRate' => 30,
            'doorBreakRate' => 40,
            'equipmentFireBreakRate' => 30,
            'startingFireRate' => 2,
            'propagatingFireRate' => 30,
            'hullFireDamageRate' => 20,
            'tremorRate' => 5,
            'electricArcRate' => 5,
            'metalPlateRate' => 5,
            'panicCrisisRate' => 5,
        ]);
    }
}
