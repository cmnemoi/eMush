<?php

declare(strict_types=1);

namespace Mush\Tests\Exploration\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Exploration\Entity\ClosedExploration;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetName;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class ExplorationServiceCest extends AbstractFunctionalTest
{
    private ExplorationServiceInterface $explorationService;
    private StatusServiceInterface $statusService;

    private GameEquipment $icarus;
    private Planet $planet;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->explorationService = $I->grabService(ExplorationServiceInterface::class);
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

        $desertSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::DESERT . '_default']);
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

        // then an exploration iand closedExploration are created
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

        // then a landing event is dispatched
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

    public function testCloseExplorationMoveIcarusToStartPlace(FunctionalTester $I): void
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

        // then icarus is moved to the start place
        $I->assertEquals($explorationStartPlace, $this->icarus->getPlace());
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

        // @TODO test those sectors when all their events are implemented
        // $I->seeInRepository(ExplorationLog::class, ['planetSectorName' => PlanetSectorEnum::DESERT]);
        // $I->seeInRepository(ExplorationLog::class, ['planetSectorName' => PlanetSectorEnum::OXYGEN]);
    }
}
