<?php

declare(strict_types=1);

namespace Mush\tests\api\MetaGame;

use Mush\Daedalus\Service\DaedalusService;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Tests\ApiTester;

final class DaedalusFillingControllerCest
{
    private DaedalusService $daedalusService;

    public function _before(ApiTester $I): void
    {
        $this->daedalusService = $I->grabService(DaedalusService::class);
    }

    public function shouldReturnDaedalusFilling(ApiTester $I): void
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        $daedalus = $this->daedalusService->createDaedalus($gameConfig, 'my_french_daedalus', LanguageEnum::FRENCH);
        $daedalus->setCycle(8);
        $I->haveInRepository($daedalus);

        $I->sendGetRequest('/filling-daedaluses');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'fr' => [
                'day' => 1,
                'cycle' => 8,
                'currentPlayers' => 0,
                'maxPlayers' => 16,
            ],
        ]);
    }
}
