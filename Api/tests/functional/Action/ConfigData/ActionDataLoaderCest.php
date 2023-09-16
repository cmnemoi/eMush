<?php

namespace Mush\Tests\functional\Action\ConfigData;

use Mush\Action\ConfigData\ActionDataLoader;
use Mush\Action\Entity\Action;
use Mush\Game\Entity\GameVariable;
use Mush\Tests\FunctionalTester;

class ActionDataLoaderCest
{
    private ActionDataLoader $actionDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->actionDataLoader = $I->grabService(ActionDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->actionDataLoader->loadConfigsData();

        // check a known action is loaded successfully with all its properties
        // Not testing array attributes nor ActionVariables because for an unknown reason it doesn't work (while it should)
        // might be related : https://github.com/Codeception/module-doctrine2/issues/60
        $I->seeInRepository(Action::class, [
            'name' => 'extract_spore',
            'actionName' => 'extract_spore',
            'target' => null,
            'scope' => 'self',
        ]);

        // check ActionVariables are loaded separately then
        $this->seeInRepositoryExtractSporeActionVariables($I);

        // check that we've loaded all the actions
        // $I->seeNumRecords(98, Action::class);
    }

    public function testLoadConfigsDataConfigAlreadyExists(FunctionalTester $I)
    {
        $this->actionDataLoader->loadConfigsData();

        $I->seeNumRecords(1, Action::class, [
            'name' => 'extract_spore',
            'actionName' => 'extract_spore',
            'target' => null,
            'scope' => 'self',
        ]);
    }

    private function seeInRepositoryExtractSporeActionVariables(FunctionalTester $I)
    {
        $I->seeInRepository(GameVariable::class, [
            'name' => 'actionPoint',
            'value' => 2,
            'minValue' => 0,
            'maxValue' => null,
        ]);
        $I->seeInRepository(GameVariable::class, [
            'name' => 'moralPoint',
            'value' => 0,
            'minValue' => 0,
            'maxValue' => null,
        ]);
        $I->seeInRepository(GameVariable::class, [
            'name' => 'movementPoint',
            'value' => 0,
            'minValue' => 0,
            'maxValue' => null,
        ]);
        $I->seeInRepository(GameVariable::class, [
            'name' => 'percentageCritical',
            'value' => 0,
            'minValue' => 0,
            'maxValue' => 100,
        ]);
        $I->seeInRepository(GameVariable::class, [
            'name' => 'percentageDirtiness',
            'value' => 100,
            'minValue' => 100,
            'maxValue' => 100,
        ]);
        $I->seeInRepository(GameVariable::class, [
            'name' => 'percentageInjury',
            'value' => 0,
            'minValue' => 0,
            'maxValue' => 100,
        ]);
        $I->seeInRepository(GameVariable::class, [
            'name' => 'percentageSuccess',
            'value' => 100,
            'minValue' => 1,
            'maxValue' => 100,
        ]);
    }
}
