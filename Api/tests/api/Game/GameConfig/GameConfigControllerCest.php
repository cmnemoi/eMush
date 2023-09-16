<?php

namespace Mush\Tests\api\Game\GameConfig;

use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\LanguageEnum;
use Mush\Tests\ApiTester;

class GameConfigControllerCest
{
    private string $url = 'game_configs';
    private GameConfig $gameConfig;

    public function _before(ApiTester $I)
    {
        $this->gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => 'default']);
    }

    public function testGetNonExistingGameConfig(ApiTester $I)
    {
        $I->loginUser('default');

        $I->sendGetRequest($this->url . '/999999999');
        $I->seeResponseCodeIs(404);
    }

    public function testGetGameConfig(ApiTester $I)
    {
        $I->loginUser('default');

        $I->sendGetRequest($this->url . '/' . $this->gameConfig->getId());
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'id' => $this->gameConfig->getId(),
            'name' => $this->gameConfig->getName(),
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

        $I->sendPutRequest($this->url . '/' . $this->gameConfig->getId());
        $I->seeResponseCodeIs(403);
    }

    public function testUpdateSucces(ApiTester $I)
    {
        $I->loginUser(ApiTester::ADMIN);

        $data = [
            'name' => 'default',
            'nbMush' => 3,
            'cyclePerGameDay' => 8,
            'cycleLength' => 180,
            'timeZone' => 'Europe/Paris',
            'maxNumberPrivateChannel' => 3,
            'language' => LanguageEnum::FRENCH,
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

        $I->sendPutRequest($this->url . '/' . $this->gameConfig->getId(), $data, true);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'id' => $this->gameConfig->getId(),
            'name' => $this->gameConfig->getName(),
        ]);
    }
}
