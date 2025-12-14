<?php

declare(strict_types=1);

namespace Mush\tests\functional\MetaGame\Query;

use Mush\Daedalus\Service\DaedalusService;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\MetaGame\Query\GetFillingDaedalusesQuery;
use Mush\MetaGame\Query\GetFillingDaedalusesQueryHandler;
use Mush\MetaGame\ViewModel\FillingDaedalusViewModel;
use Mush\Player\Service\PlayerService;
use Mush\Tests\FunctionalTester;
use Mush\User\Factory\UserFactory;

final class GetFillingDaedalusesQueryCest
{
    private DaedalusService $daedalusService;
    private PlayerService $playerService;
    private GetFillingDaedalusesQueryHandler $queryHandler;

    public function _before(FunctionalTester $I): void
    {
        $this->queryHandler = $I->grabService(GetFillingDaedalusesQueryHandler::class);
        $this->daedalusService = $I->grabService(DaedalusService::class);
        $this->playerService = $I->grabService(PlayerService::class);
    }

    public function shouldReturnDaedaluses(FunctionalTester $I): void
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        $frenchDaedalus = $this->daedalusService->createDaedalus($gameConfig, 'my_french_daedalus', LanguageEnum::FRENCH);
        $englishDaedalus = $this->daedalusService->createDaedalus($gameConfig, 'my_english_daedalus', LanguageEnum::ENGLISH);

        $user = UserFactory::createUser();
        $I->haveInRepository($user);
        $this->playerService->createPlayer($frenchDaedalus, $user, CharacterEnum::ANDIE);

        $frenchDaedalus->setCycle(8);
        $englishDaedalus->setCycle(8);
        $I->haveInRepository($frenchDaedalus);
        $I->haveInRepository($englishDaedalus);

        $results = $this->queryHandler->execute(
            new GetFillingDaedalusesQuery()
        );

        $I->assertEquals(
            [
                'en' => new FillingDaedalusViewModel(
                    day: 1,
                    cycle: 8,
                    currentPlayers: 0,
                    maxPlayers: 16,
                ),
                'fr' => new FillingDaedalusViewModel(
                    day: 1,
                    cycle: 8,
                    currentPlayers: 1,
                    maxPlayers: 16,
                ),
            ],
            $results,
        );
    }
}
