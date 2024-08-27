<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Exploration\Event;

use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class ExplorationEventCest extends AbstractExplorationTester
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function testExplorationCyclesAreIncrementedOnNewCycles(FunctionalTester $I): void
    {
        // given I have a planet to explore
        $planet = $this->createPlanet(
            sectors: [
                PlanetSectorEnum::OXYGEN,
                PlanetSectorEnum::DESERT,
                PlanetSectorEnum::SEISMIC_ACTIVITY,
            ],
            functionalTester: $I,
        );

        // given only nothing to report event can happen in seismic activity sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::SEISMIC_ACTIVITY,
            events: [PlanetSectorEvent::NOTHING_TO_REPORT => 1],
        );

        // given I have an exploration on this planet
        $exploration = $this->createExploration($planet, $this->players);

        $explorationCurrentCycleBeforeCycleChange = $exploration->getCycle();

        // when I have two exploration cycle changes
        for ($i = 0; $i < 2; ++$i) {
            $cycleEvent = new ExplorationEvent(
                $exploration,
                [EventEnum::NEW_CYCLE],
                new \DateTime(),
            );
            $this->eventService->callEvent($cycleEvent, ExplorationEvent::EXPLORATION_NEW_CYCLE);
        }

        // then exploration cycles are incremented
        $I->assertEquals(
            $explorationCurrentCycleBeforeCycleChange + 2,
            $exploration->getCycle(),
        );
    }

    public function testClosedExplorationIsFinishedWhenAllSectorsAreVisited(FunctionalTester $I): void
    {
        // given I have a planet to explore
        $planet = $this->createPlanet(
            sectors: [
                PlanetSectorEnum::OXYGEN,
                PlanetSectorEnum::DESERT,
                PlanetSectorEnum::SEISMIC_ACTIVITY,
            ],
            functionalTester: $I,
        );

        // given only nothing to report event can happen in desert sector to avoid back event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::DESERT,
            events: [PlanetSectorEvent::NOTHING_TO_REPORT => 1],
        );

        // given only nothing to report event can happen in seismic activity sector to avoid back event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::SEISMIC_ACTIVITY,
            events: [PlanetSectorEvent::NOTHING_TO_REPORT => 1],
        );

        // given I have an exploration on this planet
        $exploration = $this->createExploration($planet, $this->players);

        $closedExploration = $exploration->getClosedExploration();

        // given I have visited the 2 first sectors
        for ($i = 0; $i < 2; ++$i) {
            $cycleEvent = new ExplorationEvent(
                $exploration,
                [EventEnum::NEW_CYCLE],
                new \DateTime(),
            );
            $this->eventService->callEvent($cycleEvent, ExplorationEvent::EXPLORATION_NEW_CYCLE);
        }

        // when I have a cycle change for the last sector
        $cycleEvent = new ExplorationEvent(
            $exploration,
            [EventEnum::NEW_CYCLE],
            new \DateTime(),
        );
        $this->eventService->callEvent($cycleEvent, ExplorationEvent::EXPLORATION_NEW_CYCLE);

        // then closed exploration is finished
        $I->assertTrue($closedExploration->isExplorationFinished());

        // then I cannot see exploration in repository
        $I->dontSeeInRepository(
            entity: Exploration::class,
            params: ['planet' => $exploration->getPlanet()],
        );
    }

    public function testClosedExplorationIsFinishedWhenAllExploratorsAreDead(FunctionalTester $I): void
    {
        // given players have a spacesuit
        foreach ($this->players as $player) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: GearItemEnum::SPACESUIT,
                equipmentHolder: $player,
                reasons: [],
                time: new \DateTime(),
            );
        }

        // given I have a planet to explore
        $planet = $this->createPlanet(
            sectors: [
                PlanetSectorEnum::DESERT,
            ],
            functionalTester: $I,
        );

        // given I have an exploration on this planet
        $exploration = $this->createExploration($planet, $this->players);

        $closedExploration = $exploration->getClosedExploration();
        // given all explorators are dead
        $exploration->getExplorators()->map(
            static fn (Player $player) => $player->getPlayerInfo()->setGameStatus(GameStatusEnum::FINISHED),
        );

        // when I have a cycle change
        $cycleEvent = new ExplorationEvent(
            $exploration,
            [EventEnum::NEW_CYCLE],
            new \DateTime(),
        );
        $this->eventService->callEvent($cycleEvent, ExplorationEvent::EXPLORATION_NEW_CYCLE);

        // then closed exploration is finished
        $I->assertTrue($closedExploration->isExplorationFinished());

        // then I cannot see exploration in repository
        $I->dontSeeInRepository(
            entity: Exploration::class,
            params: ['planet' => $exploration->getPlanet()],
        );
    }

    public function testAgainEventAsLastEvent(FunctionalTester $I): void
    {
        // given Chun has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::DESERT], $I),
            explorators: new PlayerCollection([$this->chun]),
        );
        $closedExploration = $exploration->getClosedExploration();
        $desertPlanetSector = $exploration->getPlanet()->getSectors()->filter(static fn ($sector) => $sector->getName() === PlanetSectorEnum::DESERT)->first();

        // given only again event can happen in desert sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::DESERT,
            events: [PlanetSectorEvent::AGAIN => 1]
        );

        // when I have a cycle change
        $cycleEvent = new ExplorationEvent(
            $exploration,
            [EventEnum::NEW_CYCLE],
            new \DateTime(),
        );
        $this->eventService->callEvent($cycleEvent, ExplorationEvent::EXPLORATION_NEW_CYCLE);

        // then desert planet sector should be unvisited
        $I->assertFalse($desertPlanetSector->isVisited());

        // then exploration should not be finished
        $I->assertFalse($closedExploration->isExplorationFinished());
    }

    public function testExplorationEstimatedDurationWithMoreUnvisitSectorsThanSectionsToVisit(FunctionalTester $I): void
    {
        // given a planet with 20 oxygen sectors
        $sectors = [];
        for ($i = 0; $i < 20; ++$i) {
            $sectors[] = PlanetSectorEnum::OXYGEN;
        }
        $planet = $this->createPlanet($sectors, $I);

        // given Chun has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // when an exploration is created
        $exploration = $this->createExploration(
            planet: $planet,
            explorators: new PlayerCollection([$this->chun]),
        );

        // then estimated duration should be 90 minutes
        $I->assertEquals(90, $exploration->getEstimatedDuration());

        // when I have three cycle changes
        for ($i = 0; $i < 3; ++$i) {
            $cycleEvent = new ExplorationEvent(
                $exploration,
                [EventEnum::NEW_CYCLE],
                new \DateTime(),
            );
            $this->eventService->callEvent($cycleEvent, ExplorationEvent::EXPLORATION_NEW_CYCLE);
        }

        // then estimated duration should be 60 minutes
        $I->assertEquals(60, $exploration->getEstimatedDuration());
    }

    public function testExplorationEstimatedDurationWithVisitedSectors(FunctionalTester $I): void
    {
        // given a planet with 20 oxygen sectors
        $sectors = [];
        for ($i = 0; $i < 20; ++$i) {
            $sectors[] = PlanetSectorEnum::OXYGEN;
        }
        $planet = $this->createPlanet($sectors, $I);

        // given 14 sectors are visited
        foreach ($planet->getSectors()->slice(0, 14) as $sector) {
            $sector->visit();
        }

        // given Chun has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // when an exploration is created
        $exploration = $this->createExploration(
            planet: $planet,
            explorators: new PlayerCollection([$this->chun]),
        );

        // then estimated duration should be 60 minutes
        $I->assertEquals(60, $exploration->getEstimatedDuration());

        // when I have three cycle changes
        for ($i = 0; $i < 3; ++$i) {
            $cycleEvent = new ExplorationEvent(
                $exploration,
                [EventEnum::NEW_CYCLE],
                new \DateTime(),
            );
            $this->eventService->callEvent($cycleEvent, ExplorationEvent::EXPLORATION_NEW_CYCLE);
        }

        // then estimated duration should be 30 minutes
        $I->assertEquals(30, $exploration->getEstimatedDuration());
    }
}
