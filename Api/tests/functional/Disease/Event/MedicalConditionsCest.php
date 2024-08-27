<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Disease\Event;

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
final class MedicalConditionsCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
    }

    public function shouldLoseDiseasePointAtCycleChange(FunctionalTester $I): void
    {
        // given Chun has a migraine
        $disease = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::MIGRAINE,
            player: $this->chun,
            reasons: [],
        );

        // given migraine has 2 disease points
        $disease->setDiseasePoint(2);

        // when a new cycle is triggered
        $playerCycleEvent = new PlayerCycleEvent(
            player: $this->chun,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime()
        );
        $this->eventService->callEvent($playerCycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then the disorder should have 1 disease point
        $I->assertEquals(1, $disease->getDiseasePoint());
    }

    public function shouldHealAtCycleChange(FunctionalTester $I): void
    {
        // given Chun has a migraine
        $disease = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::MIGRAINE,
            player: $this->chun,
            reasons: [],
        );

        // given disease has 1 disease point
        $disease->setDiseasePoint(1);

        // when a new cycle is triggered
        $playerCycleEvent = new PlayerCycleEvent(
            player: $this->chun,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime()
        );
        $this->eventService->callEvent($playerCycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then the disease should be removed
        $I->assertNull($this->chun->getMedicalConditionByName(DiseaseEnum::MIGRAINE));
    }
}
