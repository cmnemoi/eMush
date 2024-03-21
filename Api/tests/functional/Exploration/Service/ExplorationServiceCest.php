<?php

declare(strict_types=1);

namespace Mush\Tests\Exploration\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\ClosedExploration;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetName;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class ExplorationServiceCest extends AbstractExplorationTester
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private GameEquipment $icarus;
    private Planet $planet;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given there is Icarus Bay on this Daedalus
        $icarusBay = $this->createExtraPlace(RoomEnum::ICARUS_BAY, $I, $this->daedalus);

        // given player1 and player2 are in Icarus Bay
        $this->player1->changePlace($icarusBay);
        $this->player2->changePlace($icarusBay);

        // given there is the Icarus ship in Icarus Bay
        /** @var EquipmentConfig $icarusConfig */
        $icarusConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::ICARUS]);
        $this->icarus = new GameEquipment($icarusBay);
        $this->icarus
            ->setName(EquipmentEnum::ICARUS)
            ->setEquipment($icarusConfig)
        ;
        $I->haveInRepository($this->icarus);

        // given a planet with oxygen is found
        $planetName = new PlanetName();
        $planetName->setFirstSyllable(1);
        $planetName->setFourthSyllable(1);
        $I->haveInRepository($planetName);

        $this->planet = new Planet($this->player);
        $this->planet
            ->setName($planetName)
            ->setSize(2)
        ;
        $I->haveInRepository($this->planet);

        /** @var PlanetSectorConfig $desertSectorConfig */
        $desertSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::DESERT . '_default']);
        $desertSectorConfig->setExplorationEvents([PlanetSectorEvent::NOTHING_TO_REPORT => 1]);
        $desertSector = new PlanetSector($desertSectorConfig, $this->planet);
        $I->haveInRepository($desertSector);

        $oxygenSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::OXYGEN . '_default']);
        $oxygenSector = new PlanetSector($oxygenSectorConfig, $this->planet);
        $I->haveInRepository($oxygenSector);

        $this->planet->setSectors(new ArrayCollection([$desertSector, $oxygenSector]));

        // given the Daedalus is in orbit around the planet
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
    }

    public function testCreateExplorationCreatesExplorationEntities(FunctionalTester $I): void
    {
        // when createExploration is called
        $this->explorationService->createExploration(
            players: new PlayerCollection([$this->player1, $this->player2]),
            explorationShip: $this->icarus,
            numberOfSectorsToVisit: $this->planet->getSize(),
            reasons: ['test'],
        );

        // then an exploration and closedExploration are created
        $I->seeInRepository(Exploration::class, ['planet' => $this->planet]);
        $I->seeInRepository(ClosedExploration::class);
    }

    public function testCreateExplorationDispatchLandingEvent(FunctionalTester $I): void
    {
        // when createExploration is called
        $this->explorationService->createExploration(
            players: new PlayerCollection([$this->player1, $this->player2]),
            explorationShip: $this->icarus,
            numberOfSectorsToVisit: $this->planet->getSize(),
            reasons: ['test'],
        );

        // then landing event is dispatched
        $I->seeInRepository(
            entity: ExplorationLog::class,
            params: [
                'planetSectorName' => PlanetSectorEnum::LANDING,
            ]
        );
    }

    public function testCloseExplorationDeletesExplorationEntity(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->explorationService->createExploration(
            players: new PlayerCollection([$this->player1, $this->player2]),
            explorationShip: $this->icarus,
            numberOfSectorsToVisit: $this->planet->getSize(),
            reasons: ['test'],
        );

        // when closeExploration is called
        $this->explorationService->closeExploration(
            exploration: $exploration,
            reasons: ['test'],
        );

        // then the exploration is deleted but closedExploration is not
        $I->dontSeeInRepository(Exploration::class, ['planet' => $this->planet]);
        $I->seeInRepository(ClosedExploration::class);
    }

    public function testCloseExplorationMoveExploratorsToStartPlace(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->explorationService->createExploration(
            players: new PlayerCollection([$this->player1, $this->player2]),
            explorationShip: $this->icarus,
            numberOfSectorsToVisit: $this->planet->getSize(),
            reasons: ['test'],
        );

        $explorationStartPlaceName = $exploration->getStartPlaceName();
        $explorationStartPlace = $this->daedalus->getPlaceByName($explorationStartPlaceName);

        // when closeExploration is called
        $this->explorationService->closeExploration(
            exploration: $exploration,
            reasons: ['test'],
        );

        // then the explorators are moved to the start place
        $I->assertEquals($explorationStartPlace, $this->player1->getPlace());
        $I->assertEquals($explorationStartPlace, $this->player2->getPlace());
    }

    public function testCloseExplorationMoveEquipmentFromPlanetToStartPlace(FunctionalTester $I): void
    {
        // given there are some scrap metal on the planet
        for ($i = 0; $i < 5; ++$i) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: ItemEnum::METAL_SCRAPS,
                equipmentHolder: $this->daedalus->getPlanetPlace(),
                reasons: ['test'],
                time: new \DateTime(),
            );
        }

        // given an exploration is created
        $exploration = $this->explorationService->createExploration(
            players: new PlayerCollection([$this->player1, $this->player2]),
            explorationShip: $this->icarus,
            numberOfSectorsToVisit: $this->planet->getSize(),
            reasons: ['test'],
        );

        $explorationStartPlaceName = $exploration->getStartPlaceName();
        $explorationStartPlace = $this->daedalus->getPlaceByName($explorationStartPlaceName);

        // when closeExploration is called
        $this->explorationService->closeExploration(
            exploration: $exploration,
            reasons: ['test'],
        );

        // then icarus is moved to the start place
        $I->assertTrue($explorationStartPlace->hasEquipmentByName(EquipmentEnum::ICARUS));

        // then the scrap metal is moved to the start place too
        $I->assertTrue($explorationStartPlace->hasEquipmentByName(ItemEnum::METAL_SCRAPS));
    }

    public function testDispatchExplorationEventDispatchesExplorationEvent(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->explorationService->createExploration(
            players: new PlayerCollection([$this->player1, $this->player2]),
            explorationShip: $this->icarus,
            numberOfSectorsToVisit: $this->planet->getSize(),
            reasons: ['test'],
        );

        // when dispatchExplorationEvent is called
        for ($i = 0; $i < $exploration->getNumberOfSectionsToVisit(); ++$i) {
            $this->explorationService->dispatchExplorationEvent($exploration);
        }

        // then all sectors visited have their event dispatched
        $I->seeInRepository(ExplorationLog::class, ['planetSectorName' => PlanetSectorEnum::LANDING]);
        $I->seeInRepository(ExplorationLog::class, ['planetSectorName' => PlanetSectorEnum::DESERT]);
        $I->seeInRepository(ExplorationLog::class, ['planetSectorName' => PlanetSectorEnum::OXYGEN]);
    }

    public function testCloseExplorationAddOxygenToDaedalus(FunctionalTester $I): void
    {
        // given Daedalus has 0 units of oxygen
        $this->daedalus->setOxygen(0);

        // given an exploration is created
        $exploration = $this->explorationService->createExploration(
            players: new PlayerCollection([$this->player1, $this->player2]),
            explorationShip: $this->icarus,
            numberOfSectorsToVisit: $this->planet->getSize(),
            reasons: ['test'],
        );

        // given exploration has found 8 units of oxygen
        /** @var ChargeStatus $oxygenStatus */
        $oxygenStatus = $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::EXPLORATION_OXYGEN,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $this->statusService->updateCharge(
            chargeStatus: $oxygenStatus,
            delta: 8,
            tags: [],
            time: new \DateTime(),
        );

        // when exploration is finished
        $this->explorationService->closeExploration($exploration, ['test']);

        // then oxygen is added to Daedalus
        $I->assertEquals(8, $this->daedalus->getOxygen());
    }

    public function testCloseExplorationAddFuelToDaedalus(FunctionalTester $I): void
    {
        // given Daedalus has 0 units of fuel
        $this->daedalus->setFuel(0);

        // given an exploration is created
        $exploration = $this->explorationService->createExploration(
            players: new PlayerCollection([$this->player1, $this->player2]),
            explorationShip: $this->icarus,
            numberOfSectorsToVisit: $this->planet->getSize(),
            reasons: ['test'],
        );

        // given exploration has found 8 units of fuel
        /** @var ChargeStatus $fuelStatus */
        $fuelStatus = $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::EXPLORATION_FUEL,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $this->statusService->updateCharge(
            chargeStatus: $fuelStatus,
            delta: 8,
            tags: [],
            time: new \DateTime(),
        );

        // when exploration is finished
        $this->explorationService->closeExploration($exploration, ['test']);

        // then fuel is added to Daedalus
        $I->assertEquals(8, $this->daedalus->getFuel());
    }

    public function testCloseExplorationDoesNotAddOxygenNorFuelToDaedalusIfAllExploratorsAreDead(FunctionalTester $I): void
    {
        // given an extra player so Daedalus is not finished when all explorators are dead
        $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JIN_SU);

        // given Daedalus has 0 units of oxygen
        $this->daedalus->setOxygen(0);

        // given Daedalus has 0 units of fuel
        $this->daedalus->setFuel(0);

        // given an exploration is created
        $exploration = $this->explorationService->createExploration(
            players: new PlayerCollection([$this->player1, $this->player2]),
            explorationShip: $this->icarus,
            numberOfSectorsToVisit: $this->planet->getSize(),
            reasons: ['test'],
        );

        // given exploration has found 8 units of oxygen
        /** @var ChargeStatus $oxygenStatus */
        $oxygenStatus = $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::EXPLORATION_OXYGEN,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $this->statusService->updateCharge(
            chargeStatus: $oxygenStatus,
            delta: 8,
            tags: [],
            time: new \DateTime(),
        );

        // given exploration has found 8 units of fuel
        /** @var ChargeStatus $fuelStatus */
        $fuelStatus = $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::EXPLORATION_FUEL,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $this->statusService->updateCharge(
            chargeStatus: $fuelStatus,
            delta: 8,
            tags: [],
            time: new \DateTime(),
        );

        // given all explorators are dead
        foreach ($exploration->getExplorators() as $explorator) {
            $deathEvent = new PlayerEvent($explorator, ['test', EndCauseEnum::INJURY], new \DateTime());
            $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);
        }

        // when exploration is finished
        $this->explorationService->closeExploration($exploration, ['test']);

        // then oxygen is not added to Daedalus
        $I->assertEquals(0, $this->daedalus->getOxygen());
    }

    public function testDispatchLandingEventAlwaysReturnsNothingToReportIfAPilotIsInTheExplorationTeam(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->explorationService->createExploration(
            players: new PlayerCollection([$this->player1]),
            explorationShip: $this->icarus,
            numberOfSectorsToVisit: $this->planet->getSize(),
            reasons: ['test'],
        );

        // given the player is a pilot
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::POC_PILOT_SKILL,
            holder: $this->player1,
            tags: [],
            time: new \DateTime(),
        );

        // when dispatchEvent is called
        $events = [];
        for ($i = 0; $i < $exploration->getNumberOfSectionsToVisit(); ++$i) {
            $exploration = $this->explorationService->dispatchLandingEvent($exploration);
            $events[] = $exploration->getClosedExploration()->getLogs()->last()->getEventName();
        }

        // then the first event is always landing nothing to report
        for ($i = 0; $i < $exploration->getNumberOfSectionsToVisit(); ++$i) {
            $I->assertEquals(
                expected: PlanetSectorEvent::NOTHING_TO_REPORT,
                actual: $events[$i],
            );
        }
    }

    public function testDispatchExplorationEventDoesNotDispatchAgainEventIfExplorationTeamHasACompass(FunctionalTester $I): void
    {
        // given a planet
        $planet = $this->createPlanet([PlanetSectorEnum::DESERT, PlanetSectorEnum::OXYGEN], $I);

        // given there are only nothing to report and again events on the desert sector
        $this->setupPlanetSectorEvents(
            PlanetSectorEnum::DESERT,
            [
                PlanetSectorEvent::NOTHING_TO_REPORT => 1,
                PlanetSectorEvent::AGAIN => 1,
            ]
        );

        // given Chun has a compass
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::QUADRIMETRIC_COMPASS,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given an exploration is created
        $exploration = $this->createExploration($planet, new PlayerCollection([$this->chun]));

        // when dispatchExplorationEvent is called
        for ($i = 0; $i < $exploration->getNumberOfSectionsToVisit(); ++$i) {
            $this->explorationService->dispatchExplorationEvent($exploration);
        }

        // then I don't see again event in the exploration logs
        $I->dontSeeInRepository(ExplorationLog::class, ['eventName' => PlanetSectorEvent::AGAIN]);
    }
}
