<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Achievement;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class ExplorationEventSubscriberCest extends AbstractExplorationTester
{
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;
    private StatisticRepositoryInterface $statisticRepository;
    private Exploration $exploration;
    private ClosedDaedalus $closedDaedalus;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);
        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);
        $this->closedDaedalus = $this->daedalus->getDaedalusInfo()->getClosedDaedalus();
    }

    public function shouldIncrementExploFeedStatisticWhenReturningWithFood(FunctionalTester $I): void
    {
        $this->givenAnExplorationWithFoodItems($I);

        $this->whenExplorationIsClosed();

        $this->thenBothPlayersHaveExploFeedPendingStatisticWith(2, $I);
    }

    public function shouldNotIncrementExploFeedStatisticWhenReturningWithoutFood(FunctionalTester $I): void
    {
        $this->givenAnExplorationWithoutFood($I);

        $this->whenExplorationIsClosed();

        $this->thenBothPlayersHaveNoExploFeedStatistic($I);
    }

    public function shouldNotIncrementExploFeedStatisticWhenAllExploratorsAreDead(FunctionalTester $I): void
    {
        $this->givenAnExplorationWithFoodAndAllExploratorsDead($I);

        $this->whenExplorationIsClosed();

        $this->thenBothPlayersHaveNoExploFeedStatistic($I);
    }

    private function givenAnExplorationWithFoodItems(FunctionalTester $I): void
    {
        $planet = $this->createPlanet([PlanetSectorEnum::OXYGEN], $I);
        $this->exploration = $this->createExploration($planet, $this->players);

        /** @var GameEquipmentServiceInterface $equipmentService */
        $equipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $planetPlace = $this->daedalus->getPlanetPlace();
        $equipmentService->createGameEquipmentFromName(
            GameRationEnum::ALIEN_STEAK,
            $planetPlace,
            ['exploration'],
            new \DateTime()
        );
        $equipmentService->createGameEquipmentFromName(
            GameRationEnum::COOKED_RATION,
            $planetPlace,
            ['exploration'],
            new \DateTime()
        );
        $equipmentService->createGameEquipmentFromName(
            ItemEnum::STARMAP_FRAGMENT,
            $planetPlace,
            ['exploration'],
            new \DateTime()
        );
    }

    private function givenAnExplorationWithoutFood(FunctionalTester $I): void
    {
        $planet = $this->createPlanet([PlanetSectorEnum::OXYGEN], $I);
        $this->exploration = $this->createExploration($planet, $this->players);
    }

    private function givenAnExplorationWithFoodAndAllExploratorsDead(FunctionalTester $I): void
    {
        $planet = $this->createPlanet([PlanetSectorEnum::OXYGEN], $I);
        $this->exploration = $this->createExploration($planet, $this->players);

        /** @var GameEquipmentServiceInterface $equipmentService */
        $equipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $planetPlace = $this->daedalus->getPlanetPlace();
        $equipmentService->createGameEquipmentFromName(
            GameRationEnum::ALIEN_STEAK,
            $planetPlace,
            ['exploration'],
            new \DateTime()
        );

        $this->player1->kill();
        $this->player2->kill();
    }

    private function whenExplorationIsClosed(): void
    {
        $this->explorationService->closeExploration($this->exploration, ['test']);
    }

    private function thenBothPlayersHaveExploFeedPendingStatisticWith(int $count, FunctionalTester $I): void
    {
        $player1PendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(StatisticEnum::EXPLO_FEED, $this->player1->getUser()->getId(), $this->closedDaedalus->getId());
        $player2PendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(StatisticEnum::EXPLO_FEED, $this->player2->getUser()->getId(), $this->closedDaedalus->getId());
        $player1Statistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::EXPLO_FEED, $this->player1->getUser()->getId());
        $player2Statistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::EXPLO_FEED, $this->player2->getUser()->getId());

        $I->assertNotNull($player1PendingStatistic);
        $I->assertNotNull($player2PendingStatistic);
        $I->assertNull($player1Statistic);
        $I->assertNull($player2Statistic);
        $I->assertEquals($count, $player1PendingStatistic->getCount());
        $I->assertEquals($count, $player2PendingStatistic->getCount());
    }

    private function thenBothPlayersHaveNoExploFeedStatistic(FunctionalTester $I): void
    {
        $player1PendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(StatisticEnum::EXPLO_FEED, $this->player1->getUser()->getId(), $this->closedDaedalus->getId());
        $player2PendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(StatisticEnum::EXPLO_FEED, $this->player2->getUser()->getId(), $this->closedDaedalus->getId());
        $player1Statistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::EXPLO_FEED, $this->player1->getUser()->getId());
        $player2Statistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::EXPLO_FEED, $this->player2->getUser()->getId());

        $I->assertNull($player1PendingStatistic);
        $I->assertNull($player2PendingStatistic);
        $I->assertNull($player1Statistic);
        $I->assertNull($player2Statistic);
    }
}
