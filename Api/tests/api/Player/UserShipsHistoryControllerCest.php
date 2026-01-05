<?php

declare(strict_types=1);

namespace Mush\tests\api\Player;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Tests\ApiTester;
use Mush\User\Entity\User;

final readonly class UserShipsHistoryControllerCest
{
    private DaedalusServiceInterface $daedalusService;
    private PlayerServiceInterface $playerService;
    private GameConfig $gameConfig;
    private User $user;
    private Daedalus $daedalus;

    public function _before(ApiTester $I): void
    {
        $this->user = $I->loginUser('default');
        $this->daedalusService = $I->grabService(DaedalusServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
    }

    public function shouldReturnUserShipsHistory(ApiTester $I): void
    {
        $this->givenDaedalusIsCreatedWithPlayerAndEnded();

        $this->whenIRequestUserShipsHistory($I);

        $this->thenResponseShouldContainShipsHistoryData($I);
    }

    private function givenDaedalusIsCreatedWithPlayerAndEnded(): void
    {
        $this->daedalus = $this->daedalusService->createDaedalus($this->gameConfig, 'my_daedalus', LanguageEnum::FRENCH);
        $this->playerService->createPlayer($this->daedalus, $this->user, CharacterEnum::ANDIE);
        $this->daedalusService->endDaedalus($this->daedalus, EndCauseEnum::DAEDALUS_DESTROYED, new \DateTime());
    }

    private function whenIRequestUserShipsHistory(ApiTester $I): void
    {
        $I->sendGetRequest('/players/ships-history', [
            'userId' => $this->user->getUserId(),
            'page' => 1,
            'itemsPerPage' => 10,
            'language' => LanguageEnum::FRENCH,
        ]);
    }

    private function thenResponseShouldContainShipsHistoryData(ApiTester $I): void
    {
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'data' => [
                'characterBody' => ':andie:',
                'characterName' => 'Andie',
                'daysSurvived' => 0,
                'nbExplorations' => 0,
                'nbNeronProjects' => 0,
                'nbResearchProjects' => 0,
                'nbScannedPlanets' => 0,
                'titles' => '',
                'triumph' => '0 :triumph:',
                'endCause' => 'Daedalus détruit',
                'daedalusId' => $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId(),
            ],
            'totalItems' => 1,
        ]);
    }
}
