<?php

declare(strict_types=1);

namespace Mush\tests\functional\Exploration\Event;

use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class ExplorationEventCest extends AbstractExplorationTester
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testExplorationCyclesAreIncrementedOnNewCycles(FunctionalTester $I): void
    {
        // given
        $explorationCurrrentCycleBeforeCycleChange = $this->exploration->getCycle();

        // when I have two exploration cycle changes
        for ($i = 0; $i < 2; ++$i) {
            $cycleEvent = new ExplorationEvent(
                $this->exploration,
                [EventEnum::NEW_CYCLE],
                new \DateTime(),
            );
            $this->eventService->callEvent($cycleEvent, ExplorationEvent::EXPLORATION_NEW_CYCLE);
        }

        // then exploration cycles are incremented
        $I->assertEquals(
            $explorationCurrrentCycleBeforeCycleChange + 2,
            $this->exploration->getCycle(),
        );

        $I->seeInRepository(
            entity: Exploration::class,
            params: ['planet' => $this->exploration->getPlanet()],
        );
    }

    public function testClosedExplorationIsFinishedWhenAllSectorsAreVisited(FunctionalTester $I): void
    {
        $closedExploration = $this->exploration->getClosedExploration();

        // given I visit landing sector
        $explorationEvent = new ExplorationEvent(
            exploration: $this->exploration,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($explorationEvent, ExplorationEvent::EXPLORATION_STARTED);

        // given I have visited all planet sectors minus one
        for ($i = 0; $i < $this->exploration->getPlanet()->getUnvisitedSectors()->count() - 1; ++$i) {
            $cycleEvent = new ExplorationEvent(
                $this->exploration,
                [EventEnum::NEW_CYCLE],
                new \DateTime(),
            );
            $this->eventService->callEvent($cycleEvent, ExplorationEvent::EXPLORATION_NEW_CYCLE);
        }

        // when I have a cycle change for the last sector
        $cycleEvent = new ExplorationEvent(
            $this->exploration,
            [EventEnum::NEW_CYCLE],
            new \DateTime(),
        );
        $this->eventService->callEvent($cycleEvent, ExplorationEvent::EXPLORATION_NEW_CYCLE);

        // then closed exploration is finished
        $I->assertTrue($closedExploration->isExplorationFinished());

        // then I cannot see exploration in repository
        $I->dontSeeInRepository(
            entity: Exploration::class,
            params: ['planet' => $this->exploration->getPlanet()],
        );
    }

    public function testClosedExplorationIsFinishedWhenAllExploratorsAreDead(FunctionalTester $I): void
    {
        $closedExploration = $this->exploration->getClosedExploration();
        // given all explorators are dead
        $this->exploration->getExplorators()->map(
            fn (Player $player) => $player->getPlayerInfo()->setGameStatus(GameStatusEnum::FINISHED),
        );

        // when I have a cycle change
        $cycleEvent = new ExplorationEvent(
            $this->exploration,
            [EventEnum::NEW_CYCLE],
            new \DateTime(),
        );
        $this->eventService->callEvent($cycleEvent, ExplorationEvent::EXPLORATION_NEW_CYCLE);

        // then closed exploration is finished
        $I->assertTrue($closedExploration->isExplorationFinished());

        // then I cannot see exploration in repository
        $I->dontSeeInRepository(
            entity: Exploration::class,
            params: ['planet' => $this->exploration->getPlanet()],
        );
    }
}
