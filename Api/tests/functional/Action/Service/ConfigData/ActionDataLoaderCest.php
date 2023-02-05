<?php

namespace Mush\Tests\functional\Action\Service\ConfigData;

use App\Tests\FunctionalTester;
use Mush\Action\Entity\Action;
use Mush\Action\Service\ConfigData\ActionDataLoader;

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
            'name' => 'suicide',
            'actionName' => 'suicide',
            'target' => null,
            'scope' => 'self',
        ]);

        // check that we've loaded all the actions
        $I->seeNumRecords(87, Action::class);
    }

    public function testLoadConfigsDataConfigAlreadyExists(FunctionalTester $I)
    {
        $I->haveInRepository(Action::class, [
            'name' => 'suicide',
            'actionName' => 'suicide',
            'target' => null,
            'scope' => 'self',
        ]);

        $this->actionDataLoader->loadConfigsData();

        $I->seeNumRecords(1, Action::class, [
            'name' => 'suicide',
            'actionName' => 'suicide',
            'target' => null,
            'scope' => 'self',
        ]);
    }
}
