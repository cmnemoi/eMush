<?php

declare(strict_types=1);

namespace Mush\User\Repository;

use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;
use Mush\User\Factory\UserFactory;

/**
 * @internal
 */
final class FindUserNumberOfPastGamesQueryCest extends AbstractFunctionalTest
{
    private UserRepositoryInterface $userRepository;
    private PlayerServiceInterface $playerService;
    private User $user;
    private ClosedPlayer $closedPlayer;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->userRepository = $I->grabService(UserRepositoryInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
    }

    public function shouldReturnZeroIfUserHasNoGames(FunctionalTester $I): void
    {
        // Given
        $this->givenAUser($I);

        // When
        $gamesCount = $this->whenCountingUserPastGames();

        // Then
        $this->thenUserShouldHavePlayedGames(
            expected: 0,
            actual: $gamesCount,
            I: $I
        );
    }

    public function shouldReturnOneIfUserHasOneGame(FunctionalTester $I): void
    {
        // Given
        $this->givenAUser($I);
        $this->givenAPlayerInDaedalus($I);

        // When
        $gamesCount = $this->whenCountingUserPastGames();

        // Then
        $this->thenUserShouldHavePlayedGames(
            expected: 1,
            actual: $gamesCount,
            I: $I
        );
    }

    public function shouldReturnTwoIfUserHasTwoGames(FunctionalTester $I): void
    {
        // Given
        $this->givenAUser($I);
        $this->givenAPlayerInDaedalus($I);
        $this->givenANewGameForSameUser($I);

        // When
        $gamesCount = $this->whenCountingUserPastGames();

        // Then
        $this->thenUserShouldHavePlayedGames(
            expected: 2,
            actual: $gamesCount,
            I: $I
        );
    }

    private function givenAUser(FunctionalTester $I): void
    {
        $this->user = UserFactory::createUser();
        $I->haveInRepository($this->user);
    }

    private function givenAPlayerInDaedalus(): void
    {
        $this->player = $this->playerService->createPlayer($this->daedalus, $this->user, CharacterEnum::FINOLA);
        $this->closedPlayer = $this->player->getPlayerInfo()->getClosedPlayer();
        $this->closedPlayer->setClosedDaedalus($this->daedalus->getDaedalusInfo()->getClosedDaedalus());
    }

    private function givenANewGameForSameUser(FunctionalTester $I): void
    {
        $this->createDaedalus($I);
        $this->givenAPlayerInDaedalus($I);
    }

    private function whenCountingUserPastGames(): int
    {
        return $this->userRepository->findUserNumberOfPastGames($this->user);
    }

    private function thenUserShouldHavePlayedGames(int $expected, int $actual, FunctionalTester $I): void
    {
        $I->assertEquals($expected, $actual, "User should have played {$expected} game(s)");
    }
}
