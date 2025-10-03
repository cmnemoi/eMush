<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Achievement\Listener;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class GameWithoutSleepCest extends AbstractFunctionalTest
{
    private PlayerServiceInterface $playerService;
    private StatisticRepositoryInterface $statisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);
    }

    public function shouldIncrementStatisticWhenPlayerEndsWithoutSleepingAfterDay6(FunctionalTester $I): void
    {
        $this->givenPlayerSurvivedToDay7($I);
        $this->givenPlayerNeverSlept($I);

        $this->whenPlayerEnds();

        $this->thenStatisticShouldBeIncremented($I);
    }

    public function shouldNotIncrementStatisticWhenPlayerDiesBeforeDay7(FunctionalTester $I): void
    {
        $this->givenPlayerDiedOnDay5($I);
        $this->givenPlayerNeverSlept($I);

        $this->whenPlayerEnds();

        $this->thenStatisticShouldNotExist($I);
    }

    public function shouldNotIncrementStatisticWhenPlayerSlept(FunctionalTester $I): void
    {
        $this->givenPlayerSurvivedToDay7($I);
        $this->givenPlayerSlept($I);

        $this->whenPlayerEnds();

        $this->thenStatisticShouldNotExist($I);
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

    private function thenStatisticShouldBeIncremented(FunctionalTester $I): void
    {
        $statistic = $this->statisticRepository->findByNameAndUserIdOrNull(
            StatisticEnum::GAME_WITHOUT_SLEEP,
            $this->player->getUser()->getId()
        );

        $I->assertNotNull($statistic);
        $I->assertEquals(1, $statistic->getCount());
    }

    private function thenStatisticShouldNotExist(FunctionalTester $I): void
    {
        $statistic = $this->statisticRepository->findByNameAndUserIdOrNull(
            StatisticEnum::GAME_WITHOUT_SLEEP,
            $this->player->getUser()->getId()
        );

        $I->assertNull($statistic);
    }
}
