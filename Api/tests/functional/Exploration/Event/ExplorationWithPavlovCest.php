<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Exploration\Event;

use Mush\Equipment\Entity\Npc;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class ExplorationWithPavlovCest extends AbstractExplorationTester
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private Npc $pavlov;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testPavlovJoinsExpedition(FunctionalTester $I): void
    {
        // given Chun has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given I have a planet to explore
        $planet = $this->createPlanet(
            sectors: [
                PlanetSectorEnum::DESERT,
            ],
            functionalTester: $I,
        );

        // given Pavlov is in Icarus
        $this->pavlov = $this->createEquipment(ItemEnum::PAVLOV, $this->daedalus->getPlaceByName(RoomEnum::ICARUS_BAY));

        // when exploration starts
        $this->createExploration($planet, $this->players);

        // then Pavlov is on Planet
        $I->assertTrue($this->pavlov->getPlace()->getName() === RoomEnum::PLANET);

        // then Pavlov left a log in the room
        $I->canSeeInRepository(
            entity: RoomLog::class,
            params: [
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::PAVLOV_JOINED_EXPEDITION,
            ]
        );
    }

    public function testPavlovForcesRevisit(FunctionalTester $I): void
    {
        // given I have a planet to explore
        $planet = $this->createPlanet(
            sectors: [
                PlanetSectorEnum::DESERT,
                PlanetSectorEnum::FRUIT_TREES,
            ],
            functionalTester: $I,
        );

        // given Chun has a spacesuit
        $this->createEquipment(GearItemEnum::SPACESUIT, $this->chun);

        // given Chun has a compass so no Again event on desert
        $this->createEquipment(ItemEnum::QUADRIMETRIC_COMPASS, $this->chun);

        // given Pavlov is in Icarus
        $this->pavlov = $this->createEquipment(ItemEnum::PAVLOV, $this->daedalus->getPlaceByName(RoomEnum::ICARUS_BAY));

        // given I have an exploration on this planet
        $exploration = $this->createExploration($planet, $this->players);

        // given Pavlov will always dispatch a revisit
        $this->pavlov->addDataToMemory('revisit_chance', 100);

        // when I explore more times than I have sectors
        for ($i = 0; $i < 3; ++$i) {
            $cycleEvent = new ExplorationEvent(
                $exploration,
                [EventEnum::NEW_CYCLE],
                new \DateTime(),
            );
            $this->eventService->callEvent($cycleEvent, ExplorationEvent::EXPLORATION_NEW_CYCLE);
        }

        // then next sector should be visited
        $I->assertTrue($exploration->getNextSectorOrThrow()->isVisited());
        $I->assertFalse($exploration->isFinished());

        // then Pavlov left a log in the room
        $I->canSeeInRepository(
            entity: RoomLog::class,
            params: [
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::PAVLOV_TRIGGERED_SECTOR_REVISIT,
            ]
        );
    }

    public function testPavlovCantForceRevisitInvalidSectors(FunctionalTester $I): void
    {
        // given I have a planet to explore with only sectors invalid for revisiting
        $planet = $this->createPlanet(
            sectors: [
                PlanetSectorEnum::OXYGEN,
                PlanetSectorEnum::OXYGEN,
            ],
            functionalTester: $I,
        );

        // given Pavlov is in Icarus
        $this->pavlov = $this->createEquipment(ItemEnum::PAVLOV, $this->daedalus->getPlaceByName(RoomEnum::ICARUS_BAY));

        // given I have an exploration on this planet
        $exploration = $this->createExploration($planet, $this->players);

        // given Pavlov will always dispatch a revisit
        $this->pavlov->addDataToMemory('revisit_chance', 100);

        // when I explore once
        $cycleEvent = new ExplorationEvent(
            $exploration,
            [EventEnum::NEW_CYCLE],
            new \DateTime(),
        );
        $this->eventService->callEvent($cycleEvent, ExplorationEvent::EXPLORATION_NEW_CYCLE);

        // then next sector should still be new
        $I->assertFalse($exploration->getNextSectorOrThrow()->isVisited());
        $I->assertFalse($exploration->isFinished());

        // then Pavlov did not say there will be a revisit
        $I->cantSeeInRepository(
            entity: RoomLog::class,
            params: [
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::PAVLOV_TRIGGERED_SECTOR_REVISIT,
            ]
        );
    }
}
