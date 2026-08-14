<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Exploration\Event;

use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class SummerEventSubscriberCest extends AbstractExplorationTester
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testSummerEventPlanetRegenerateAfterEveryExploration(FunctionalTester $I): void
    {
        // given we are in orbit of the event planet
        $this->statusService->createStatusFromName(DaedalusStatusEnum::IN_ORBIT_OF_EVENT_PLANET, $this->daedalus, [], new \DateTime());

        // given I have a planet to explore
        $planet = $this->createPlanet(
            sectors: [
                PlanetSectorEnum::OXYGEN,
            ],
            functionalTester: $I,
        );

        // given I have an exploration on this planet
        $exploration = $this->createExploration($planet, $this->players);
        $sectors = $exploration->getPlanet()->getSectors()->toArray();

        // when exploration cycles is advanced
        $cycleEvent = new ExplorationEvent(
            $exploration,
            [EventEnum::NEW_CYCLE],
            new \DateTime(),
        );
        $this->eventService->callEvent($cycleEvent, ExplorationEvent::EXPLORATION_NEW_CYCLE);

        // then after the exploration, the planet should be regenerated
        $I->assertNotEquals(
            $sectors,
            $exploration->getPlanet()->getSectors()->toArray(),
        );
    }

    public function testNormalPlanetDoNotRegenerateAfterEveryExploration(FunctionalTester $I): void
    {
        // given I have a planet to explore
        $planet = $this->createPlanet(
            sectors: [
                PlanetSectorEnum::OXYGEN,
            ],
            functionalTester: $I,
        );

        // given I have an exploration on this planet
        $exploration = $this->createExploration($planet, $this->players);
        $sectors = $exploration->getPlanet()->getSectors()->toArray();

        // when exploration cycles is advanced
        $cycleEvent = new ExplorationEvent(
            $exploration,
            [EventEnum::NEW_CYCLE],
            new \DateTime(),
        );
        $this->eventService->callEvent($cycleEvent, ExplorationEvent::EXPLORATION_NEW_CYCLE);

        // then after the exploration, the planet should be regenerated
        $I->assertEquals(
            $sectors,
            $exploration->getPlanet()->getSectors()->toArray(),
        );
    }
}
