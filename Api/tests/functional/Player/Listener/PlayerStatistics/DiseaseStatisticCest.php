<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Player\Listener\PlayerStatistics;

use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DiseaseStatisticCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
    }

    public function shouldSameDiseaseNotIncreaseDiseaseCount(FunctionalTester $I): void
    {
        $this->whenChunGetsActiveFlu();

        $this->thenChunShouldHaveIllnessCount(1, $I);

        $this->whenChunGetsActiveFlu();

        $this->thenChunShouldHaveIllnessCount(1, $I);
    }

    public function shouldDifferentDiseaseIncreaseDiseaseCount(FunctionalTester $I): void
    {
        $this->whenChunGetsActiveFlu();

        $this->thenChunShouldHaveIllnessCount(1, $I);

        $this->whenChunGetsActiveCold();

        $this->thenChunShouldHaveIllnessCount(2, $I);
    }

    public function shouldDelayedIllnessCountOnActivating(FunctionalTester $I): void
    {
        $this->whenChunGetsDelayedFluByOneCycle();

        $this->thenChunShouldHaveIllnessCount(0, $I);

        $this->whenCyclePassesForChun();

        $this->thenChunShouldHaveIllnessCount(1, $I);
    }

    private function whenChunGetsActiveFlu(): void
    {
        $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::FLU,
            player: $this->chun,
            reasons: [],
        );
    }

    private function whenChunGetsActiveCold(): void
    {
        $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::COLD,
            player: $this->chun,
            reasons: [],
        );
    }

    private function whenChunGetsDelayedFluByOneCycle(): void
    {
        $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::FLU,
            player: $this->chun,
            reasons: [],
            delayMin: 1,
            delayLength: 0,
        );
    }

    private function whenCyclePassesForChun(): void
    {
        $playerCycleEvent = new PlayerCycleEvent(
            player: $this->chun,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($playerCycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);
    }

    private function thenChunShouldHaveIllnessCount(int $expectedCount, FunctionalTester $I): void
    {
        $I->assertEquals($expectedCount, $this->chun->getPlayerInfo()->getStatistics()->getIllnessCount());
    }
}
