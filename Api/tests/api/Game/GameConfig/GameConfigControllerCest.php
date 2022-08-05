<?php

namespace Mush\Tests\api\Game\GameConfig;

use App\Tests\ApiTester;
use Mush\Game\Entity\GameConfig;

class GameConfigControllerCest
{
    private string $url = 'game-config';

    public function testGetNonExistingGameConfig(ApiTester $I)
    {
        $I->loginUser('default');

        $I->sendGetRequest($this->url . '/999999999');
        $I->seeResponseCodeIs(404);
    }

    public function testGetGameConfig(ApiTester $I)
    {
        $I->loginUser('default');

        $gameConfig = $this->createGameConfig();
        $I->haveInRepository($gameConfig);

        $I->sendGetRequest($this->url . '/' . $gameConfig->getId());
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'id' => $gameConfig->getId(),
            'name' => $gameConfig->getName(),
        ]);
    }

    public function testUpdateNonExistingGameConfig(ApiTester $I)
    {
        $I->loginUser('default');

        $I->sendGetRequest($this->url . '/999999999');
        $I->seeResponseCodeIs(404);
    }

    public function testUpdateGameConfigNotPermitted(ApiTester $I)
    {
        $I->loginUser(ApiTester::USER);

        $gameConfig = $this->createGameConfig();
        $I->haveInRepository($gameConfig);

        $I->sendPutRequest($this->url . '/' . $gameConfig->getId());
        $I->seeResponseCodeIs(403);
    }

    public function testUpdateSucces(ApiTester $I)
    {
        $I->loginUser(ApiTester::ADMIN);

        $gameConfig = $this->createGameConfig();
        $I->haveInRepository($gameConfig);

        $data = [
            'name' => 'default',
            'nbMush' => 3,
            'cyclePerGameDay' => 8,
            'cycleLength' => 180,
            'timeZone' => 'Europe/Paris',
            'maxNumberPrivateChannel' => 3,
            'language' => 'Fr-fr',
            'initHealthPoint' => 14,
            'maxHealthPoint' => 14,
            'initMoralPoint' => 14,
            'maxMoralPoint' => 14,
            'initSatiety' => 0,
            'initActionPoint' => 8,
            'maxActionPoint' => 12,
            'initMovementPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 3,
        ];

        $I->sendPutRequest($this->url . '/' . $gameConfig->getId(), $data, true);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'id' => $gameConfig->getId(),
            'name' => $gameConfig->getName(),
        ]);
    }

    private function createGameConfig(): GameConfig
    {
        $gameConfig = new GameConfig();

        $gameConfig
            ->setName('default')
            ->setNbMush(3)
            ->setCyclePerGameDay(8)
            ->setCycleLength(60 * 3)
            ->setTimeZone('Europe/Paris')
            ->setLanguage('Fr-fr')
            ->setMaxNumberPrivateChannel(3)
            ->setInitHealthPoint(14)
            ->setMaxHealthPoint(14)
            ->setInitMoralPoint(14)
            ->setMaxMoralPoint(14)
            ->setInitSatiety(0)
            ->setInitActionPoint(8)
            ->setMaxActionPoint(12)
            ->setInitMovementPoint(12)
            ->setMaxMovementPoint(12)
            ->setMaxItemInInventory(3)
        ;

        return $gameConfig;
    }
}
