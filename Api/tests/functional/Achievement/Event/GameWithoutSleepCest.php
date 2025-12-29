<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Achievement\Listener;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class GameWithoutSleepCest extends AbstractFunctionalTest
{
    private PlayerServiceInterface $playerService;
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);
    }

    public function shouldIncrementStatisticWhenPlayerEndsWithoutSleepingAfterDay6(FunctionalTester $I): void
    {
        $this->givenPlayerSurvivedToDay7($I);
        $this->givenPlayerNeverSlept($I);

        $this->whenPlayerEnds();

        $this->thenPendingStatisticShouldBeIncremented($I);
    }

    public function shouldNotIncrementStatisticWhenPlayerDiesBeforeDay7(FunctionalTester $I): void
    {
        $this->givenPlayerDiedOnDay5($I);
        $this->givenPlayerNeverSlept($I);

        $this->whenPlayerEnds();

        $this->thenPendingStatisticShouldNotExist($I);
    }

    public function shouldNotIncrementStatisticWhenPlayerSlept(FunctionalTester $I): void
    {
        $this->givenPlayerSurvivedToDay7($I);
        $this->givenPlayerSlept($I);

        $this->whenPlayerEnds();

        $this->thenPendingStatisticShouldNotExist($I);
    }

    private function givenPlayerSurvivedToDay7(): void
    {
        $this->daedalus->setDay(7);
        $this->player->getPlayerInfo()->getClosedPlayer()->setDayCycleDeath($this->daedalus);
    }

    private function givenPlayerDiedOnDay5(): void
    {
        $this->daedalus->setDay(5);
        $this->player->getPlayerInfo()->getClosedPlayer()->setDayCycleDeath($this->daedalus);
    }

    private function givenPlayerNeverSlept(FunctionalTester $I): void
    {
        $I->assertEquals(0, $this->player->getPlayerInfo()->getStatistics()->getSleptCycles());
    }

    private function givenPlayerSlept(FunctionalTester $I): void
    {
        $this->player->getPlayerInfo()->getStatistics()->incrementSleptByCycle();
        $I->assertGreaterThan(0, $this->player->getPlayerInfo()->getStatistics()->getSleptCycles());
    }

    private function whenPlayerEnds(): void
    {
        $this->playerService->endPlayer(
            player: $this->player,
            message: 'Test message',
            likedPlayers: []
        );
    }

    private function thenPendingStatisticShouldBeIncremented(FunctionalTester $I): void
    {
        $statistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            StatisticEnum::GAME_WITHOUT_SLEEP,
            $this->player->getUser()->getId(),
            $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        );

        $I->assertNotNull($statistic);
        $I->assertEquals(1, $statistic->getCount());
    }

    private function thenPendingStatisticShouldNotExist(FunctionalTester $I): void
    {
        $statistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            StatisticEnum::GAME_WITHOUT_SLEEP,
            $this->player->getUser()->getId(),
            $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        );

        $I->assertNull($statistic);
    }
}
