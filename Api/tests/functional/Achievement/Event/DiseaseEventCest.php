<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Achievement\Service;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DiseaseEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);

        $this->daedalus->getGameConfig()->getDifficultyConfig()->setCycleDiseaseRate(0);
    }

    public function shouldIncrementDiseaseContractedPendingStatisticWhenGettingIll(FunctionalTester $I): void
    {
        $this->givenPlayerHasDiseaseAppearByName(DiseaseEnum::FLU);

        $this->thenPlayerShouldHaveOnePointOfPendingStatistic(StatisticEnum::DISEASE_CONTRACTED, $I);

        $this->whenCycleIsProgressedForPlayer();

        $this->thenPlayerShouldHaveOnePointOfPendingStatistic(StatisticEnum::DISEASE_CONTRACTED, $I);
    }

    public function shouldIncrementDiseaseContractedPendingStatisticWhenGettingIllAfterDelay(FunctionalTester $I): void
    {
        $this->givenPlayerGetsDiseaseByNameWithDelay(DiseaseEnum::FLU, 2);
        // 2 cycles until the disease appears
        $this->thenPlayerShouldNotHavePendingStatistic(StatisticEnum::DISEASE_CONTRACTED, $I);

        $this->whenCycleIsProgressedForPlayer();
        // 1 cycle until the disease appears
        $this->thenPlayerShouldNotHavePendingStatistic(StatisticEnum::DISEASE_CONTRACTED, $I);

        $this->whenCycleIsProgressedForPlayer();
        // the disease has appeared
        $this->thenPlayerShouldHaveOnePointOfPendingStatistic(StatisticEnum::DISEASE_CONTRACTED, $I);
    }

    public function shouldNotIncrementDiseaseContractedPendingStatisticWhenGettingDisorder(FunctionalTester $I): void
    {
        $this->givenPlayerGetsDiseaseByNameWithDelay(DisorderEnum::SPLEEN, 1);

        $this->thenPlayerShouldNotHavePendingStatistic(StatisticEnum::DISEASE_CONTRACTED, $I);
        $this->thenPlayerHasActiveDiseasesOfAmount(0, $I);

        $this->whenCycleIsProgressedForPlayer();

        $this->thenPlayerShouldNotHavePendingStatistic(StatisticEnum::DISEASE_CONTRACTED, $I);
        $this->thenPlayerHasActiveDiseasesOfAmount(1, $I);
    }

    private function givenPlayerHasDiseaseAppearByName(string $diseaseName): void
    {
        $this->givenPlayerGetsDiseaseByNameWithDelay($diseaseName, 0);
    }

    private function givenPlayerGetsDiseaseByNameWithDelay(string $diseaseName, int $delay): void
    {
        $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: $diseaseName,
            player: $this->player,
            reasons: ['test'],
            delayMin: $delay,
            delayLength: 0,
        );
    }

    private function whenCycleIsProgressedForPlayer(): void
    {
        $cycleEvent = new PlayerCycleEvent($this->player, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($cycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);
    }

    private function thenPlayerShouldHaveOnePointOfPendingStatistic(StatisticEnum $statisticName, FunctionalTester $I): void
    {
        $pendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            name: $statisticName,
            userId: $this->player->getUser()->getId(),
            closedDaedalusId: $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId(),
        );
        $I->assertEquals(1, $pendingStatistic?->getCount());
    }

    private function thenPlayerShouldNotHavePendingStatistic(StatisticEnum $statisticName, FunctionalTester $I): void
    {
        $pendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            name: $statisticName,
            userId: $this->player->getUser()->getId(),
            closedDaedalusId: $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId(),
        );
        $I->assertNull($pendingStatistic);
    }

    private function thenPlayerHasActiveDiseasesOfAmount(int $expectedCount, FunctionalTester $I): void
    {
        $I->assertEquals($expectedCount, $this->player->getMedicalConditions()->getActiveDiseases()->count());
    }
}
