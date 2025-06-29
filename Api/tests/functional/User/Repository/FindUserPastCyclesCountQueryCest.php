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
final class FindUserPastCyclesCountQueryCest extends AbstractFunctionalTest
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
        $cyclesCount = $this->whenCountingUserPastCycles();

        // Then
        $this->thenUserShouldHaveSurvivedCycles(
            expected: 0,
            actual: $cyclesCount,
            I: $I
        );
    }

    public function shouldReturnOneIfUserHasSurvivedOneCycleInOneGame(FunctionalTester $I): void
    {
        // Given
        $this->givenAUser($I);
        $this->givenAPlayerInDaedalus($I);
        $this->givenPlayerSurvivedExactlyOneCycle($I);

        // When
        $cyclesCount = $this->whenCountingUserPastCycles();

        // Then
        $this->thenUserShouldHaveSurvivedCycles(
            expected: 1,
            actual: $cyclesCount,
            I: $I
        );
    }

    public function shouldReturnZeroIfUserHasSurvivedOneIncompleteCycleInOneGame(FunctionalTester $I): void
    {
        // Given
        $this->givenAUser($I);
        $this->givenAPlayerInDaedalus($I);
        $this->givenPlayerSurvivedIncompleteCycle($I);

        // When
        $cyclesCount = $this->whenCountingUserPastCycles();

        // Then
        $this->thenUserShouldHaveSurvivedCycles(
            expected: 0,
            actual: $cyclesCount,
            I: $I
        );
    }

    public function shouldReturnOneIfUserHasSurvivedOneCycleAndOneIncompleteCycleInOneGame(FunctionalTester $I): void
    {
        // Given
        $this->givenAUser($I);
        $this->givenAPlayerInDaedalus($I);
        $this->givenPlayerSurvivedOneCycleAndOneIncompleteCycle($I);

        // When
        $cyclesCount = $this->whenCountingUserPastCycles();

        // Then
        $this->thenUserShouldHaveSurvivedCycles(
            expected: 1,
            actual: $cyclesCount,
            I: $I
        );
    }

    public function shouldReturnZeroIfUserSurvivedOneMinuteBeforeCycleCompletion(FunctionalTester $I): void
    {
        // Given
        $this->givenAUser($I);
        $this->givenAPlayerInDaedalus($I);
        $this->givenPlayerSurvivedOneMinuteBeforeCycle($I);

        // When
        $cyclesCount = $this->whenCountingUserPastCycles();

        // Then
        $this->thenUserShouldHaveSurvivedCycles(
            expected: 0,
            actual: $cyclesCount,
            I: $I
        );
    }

    public function shouldReturnOneIfUserSurvivedOneMinuteAfterCycleCompletion(FunctionalTester $I): void
    {
        // Given
        $this->givenAUser($I);
        $this->givenAPlayerInDaedalus($I);
        $this->givenPlayerSurvivedOneMinuteAfterCycle($I);

        // When
        $cyclesCount = $this->whenCountingUserPastCycles();

        // Then
        $this->thenUserShouldHaveSurvivedCycles(
            expected: 1,
            actual: $cyclesCount,
            I: $I
        );
    }

    public function shouldReturnTenIfUserSurvivedTenCompleteCycles(FunctionalTester $I): void
    {
        // Given
        $this->givenAUser($I);
        $this->givenAPlayerInDaedalus($I);
        $this->givenPlayerSurvivedExactNumberOfCycles($I, 10);

        // When
        $cyclesCount = $this->whenCountingUserPastCycles();

        // Then
        $this->thenUserShouldHaveSurvivedCycles(
            expected: 10,
            actual: $cyclesCount,
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

    private function givenPlayerSurvivedExactlyOneCycle(FunctionalTester $I): void
    {
        $oneCycle = $this->daedalus->getDaedalusConfig()->getCycleLength();
        $this->closedPlayer->setFinishedAt($this->player->getCreatedAt()->add(new \DateInterval('PT' . $oneCycle . 'M')));
        $I->haveInRepository($this->closedPlayer);
    }

    private function givenPlayerSurvivedExactlyTwoCycles(FunctionalTester $I): void
    {
        $oneCycle = $this->daedalus->getDaedalusConfig()->getCycleLength();
        $this->closedPlayer->setFinishedAt($this->player->getCreatedAt()->add(new \DateInterval('PT' . (2 * $oneCycle) . 'M')));
        $I->haveInRepository($this->closedPlayer);
    }

    private function givenPlayerSurvivedIncompleteCycle(FunctionalTester $I): void
    {
        $oneCycle = $this->daedalus->getDaedalusConfig()->getCycleLength();
        $this->closedPlayer->setFinishedAt($this->player->getCreatedAt()->add(new \DateInterval('PT' . ($oneCycle - 1) . 'M')));
        $I->haveInRepository($this->closedPlayer);
    }

    private function givenPlayerSurvivedOneCycleAndOneIncompleteCycle(FunctionalTester $I): void
    {
        $oneCycle = $this->daedalus->getDaedalusConfig()->getCycleLength();
        $oneCycleMinusOneMinute = $oneCycle - 1;
        $this->closedPlayer->setFinishedAt($this->player->getCreatedAt()->add(new \DateInterval('PT' . ($oneCycle + $oneCycleMinusOneMinute) . 'M')));
        $I->haveInRepository($this->closedPlayer);
    }

    private function givenPlayerSurvivedOneMinuteBeforeCycle(FunctionalTester $I): void
    {
        $oneCycle = $this->daedalus->getDaedalusConfig()->getCycleLength();
        $this->closedPlayer->setFinishedAt($this->player->getCreatedAt()->add(new \DateInterval('PT' . ($oneCycle - 1) . 'M')));
        $I->haveInRepository($this->closedPlayer);
    }

    private function givenPlayerSurvivedOneMinuteAfterCycle(FunctionalTester $I): void
    {
        $oneCycle = $this->daedalus->getDaedalusConfig()->getCycleLength();
        $this->closedPlayer->setFinishedAt($this->player->getCreatedAt()->add(new \DateInterval('PT' . ($oneCycle + 1) . 'M')));
        $I->haveInRepository($this->closedPlayer);
    }

    private function givenPlayerSurvivedExactNumberOfCycles(FunctionalTester $I, int $cycles): void
    {
        $oneCycle = $this->daedalus->getDaedalusConfig()->getCycleLength();
        $this->closedPlayer->setFinishedAt($this->player->getCreatedAt()->add(new \DateInterval('PT' . ($cycles * $oneCycle) . 'M')));
        $I->haveInRepository($this->closedPlayer);
    }

    private function whenCountingUserPastCycles(): int
    {
        return $this->userRepository->findUserPastCyclesCount($this->user);
    }

    private function thenUserShouldHaveSurvivedCycles(int $expected, int $actual, FunctionalTester $I): void
    {
        $I->assertEquals($expected, $actual, "User should have survived {$expected} cycle(s)");
    }
}
