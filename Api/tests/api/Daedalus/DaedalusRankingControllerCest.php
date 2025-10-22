<?php

declare(strict_types=1);

namespace Mush\tests\api\Daedalus;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Tests\ApiTester;

final readonly class DaedalusRankingControllerCest
{
    private DaedalusServiceInterface $daedalusService;
    private GameConfig $gameConfig;
    private Daedalus $daedalus;

    public function _before(ApiTester $I): void
    {
        $I->loginUser('default');
        $this->daedalusService = $I->grabService(DaedalusServiceInterface::class);
        $this->gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
    }

    public function shouldReturnDaedalusRanking(ApiTester $I): void
    {
        $this->givenDaedalusIsCreatedAndEnded();

        $this->whenIRequestDaedalusRanking($I);

        $this->thenResponseShouldContainRankingData($I);
    }

    private function givenDaedalusIsCreatedAndEnded(): void
    {
        $this->daedalus = $this->daedalusService->createDaedalus($this->gameConfig, 'my_daedalus', LanguageEnum::FRENCH);
        $this->daedalusService->endDaedalus($this->daedalus, EndCauseEnum::DAEDALUS_DESTROYED, new \DateTime());
    }

    private function whenIRequestDaedalusRanking(ApiTester $I): void
    {
        $I->sendGetRequest('/daedaluses/ranking', [
            'language' => LanguageEnum::FRENCH,
            'page' => 1,
            'itemsPerPage' => 10,
        ]);
    }

    private function thenResponseShouldContainRankingData(ApiTester $I): void
    {
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'data' => [
                [
                    'id' => $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId(),
                    'endCause' => 'Daedalus détruit',
                    'daysSurvived' => 0,
                    'cyclesSurvived' => 0,
                    'humanTriumphSum' => '0 :triumph:',
                    'mushTriumphSum' => '0 :triumph_mush:',
                    'highestHumanTriumph' => '0 :triumph:',
                    'highestMushTriumph' => '0 :triumph_mush:',
                ],
            ],
            'totalItems' => 1,
        ]);
    }
}
