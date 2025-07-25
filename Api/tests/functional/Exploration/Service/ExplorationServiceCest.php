<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Exploration\Service;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\GearItemEnum;
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
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Project\Enum\ProjectName;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class ExplorationServiceCest extends AbstractExplorationTester
{
    private ChooseSkillUseCase $chooseSkillUseCase;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;

    private GameEquipment $icarus;
    private Planet $planet;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
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
            ->setEquipment($icarusConfig);
        $I->haveInRepository($this->icarus);

        // given a planet with oxygen is found
        $planetName = new PlanetName();
        $planetName->setFirstSyllable(1);
        $planetName->setFourthSyllable(1);
        $I->haveInRepository($planetName);

        $this->planet = new Planet($this->player);
        $this->planet
            ->setName($planetName)
            ->setSize(2);
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

    public function testCloseExplorationNotifyExplorators(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->explorationService->createExploration(
            players: new PlayerCollection([$this->player1, $this->player2]),
            explorationShip: $this->icarus,
            numberOfSectorsToVisit: $this->planet->getSize(),
            reasons: ['test'],
        );

        // then the explorators have no notification
        $I->assertFalse($this->player1->hasNotification());
        $I->assertFalse($this->player2->hasNotification());

        // when closeExploration is called
        $this->explorationService->closeExploration(
            exploration: $exploration,
            reasons: ['test'],
        );

        // then the explorators should be given exploration finished notification
        $I->assertTrue($this->player1->hasNotificationByMessage(PlayerNotificationEnum::EXPLORATION_CLOSED->toString()));
        $I->assertTrue($this->player2->hasNotificationByMessage(PlayerNotificationEnum::EXPLORATION_CLOSED->toString()));
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
            $this->playerService->killPlayer(
                player: $explorator,
                endReason: EndCauseEnum::INJURY,
                time: new \DateTime(),
            );
        }

        // when exploration is finished
        $this->explorationService->closeExploration($exploration, ['test']);

        // then oxygen is not added to Daedalus
        $I->assertEquals(0, $this->daedalus->getOxygen());
    }

    public function testDispatchLandingEventAlwaysReturnsNothingToReportIfAPilotIsInTheExplorationTeam(FunctionalTester $I): void
    {
        // given terrence is a pilot
        $terrence = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::TERRENCE);
        $this->addSkillToPlayer(SkillEnum::PILOT, $I, $terrence);

        // given an exploration is created
        $exploration = $this->explorationService->createExploration(
            players: new PlayerCollection([$terrence]),
            explorationShip: $this->icarus,
            numberOfSectorsToVisit: $this->planet->getSize(),
            reasons: ['test'],
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
        $desertSector = $this->setupPlanetSectorEvents(
            PlanetSectorEnum::DESERT,
            [
                PlanetSectorEvent::NOTHING_TO_REPORT => 1,
                PlanetSectorEvent::AGAIN => PHP_INT_MAX - 1,
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
        for ($i = 0; $i < 2; ++$i) {
            $this->explorationService->dispatchExplorationEvent($exploration);
        }

        // then I don't see again event in the exploration logs
        $I->dontSeeInRepository(ExplorationLog::class, ['eventName' => PlanetSectorEvent::AGAIN]);

        // then desert sector still has again event
        $I->assertEquals(PHP_INT_MAX - 1, $desertSector->getExplorationEvents()[PlanetSectorEvent::AGAIN]);
    }

    public function testCloseExplorationDoesNotReturnDeadPlayerEquipmentInDaedalus(FunctionalTester $I): void
    {
        // given a planet with 1 desert sector
        $planet = $this->createPlanet([PlanetSectorEnum::DESERT], $I);

        // given Chun has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $planet,
            explorators: new PlayerCollection([$this->chun, $this->kuanTi]),
        );

        // given Chun dies
        $this->playerService->killPlayer(
            player: $this->chun,
            endReason: EndCauseEnum::mapEndCause([PlanetSectorEvent::PLANET_SECTOR_EVENT, EndCauseEnum::INJURY]),
            time: new \DateTime(),
        );

        // when exploration is closed
        $this->explorationService->closeExploration($exploration, ['test']);

        // then I should not see the spacesuit in Icarus Bay
        $I->assertFalse(
            $this->daedalus
                ->getPlaceByNameOrThrow(RoomEnum::ICARUS_BAY)
                ->hasEquipmentByName(GearItemEnum::SPACESUIT)
        );
    }

    public function closeExplorationShouldReturnOnlyTheIcarusWithTheAutoReturnProject(FunctionalTester $I): void
    {
        // given Auto Return Icarus project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::AUTO_RETURN_ICARUS),
            author: $this->player,
            I: $I,
        );

        // given a planet with 1 desert sector
        $planet = $this->createPlanet([PlanetSectorEnum::DESERT], $I);

        // given Chun has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $planet,
            explorators: new PlayerCollection([$this->chun]),
        );

        // given I have some steaks on the planet
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::ALIEN_STEAK,
            equipmentHolder: $this->daedalus->getPlanetPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given Chun dies
        $this->playerService->killPlayer(
            player: $this->chun,
            endReason: EndCauseEnum::INJURY,
            time: new \DateTime(),
        );

        // when exploration is closed
        $this->explorationService->closeExploration($exploration, ['test']);

        // then I should see the steaks in Icarus Bay
        $I->assertFalse(
            $this->daedalus
                ->getPlaceByNameOrThrow(RoomEnum::ICARUS_BAY)
                ->hasEquipmentByName(GameRationEnum::ALIEN_STEAK)
        );

        // then I should see Icarus in Icarus Bay
        $I->assertTrue(
            $this->daedalus
                ->getPlaceByNameOrThrow(RoomEnum::ICARUS_BAY)
                ->hasEquipmentByName(EquipmentEnum::ICARUS)
        );

        // then I should not see the Chun's spacesuit in Icarus Bay
        $I->assertFalse(
            $this->daedalus
                ->getPlaceByNameOrThrow(RoomEnum::ICARUS_BAY)
                ->hasEquipmentByName(GearItemEnum::SPACESUIT)
        );
    }

    public function closeExplorationShouldDestroyEverythingOnThePlanetIfExplorationFailed(FunctionalTester $I): void
    {
        // given a planet with 1 desert sector
        $planet = $this->createPlanet([PlanetSectorEnum::DESERT], $I);

        // given Chun has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $planet,
            explorators: new PlayerCollection([$this->chun]),
        );

        // given I have some steaks on the planet
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::ALIEN_STEAK,
            equipmentHolder: $this->daedalus->getPlanetPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given Chun dies
        $this->playerService->killPlayer(
            player: $this->chun,
            endReason: EndCauseEnum::INJURY,
            time: new \DateTime(),
        );

        // when exploration is closed
        $this->explorationService->closeExploration($exploration, ['test']);

        // then the steaks should not be on the planet
        $I->assertFalse(
            $this->daedalus
                ->getPlaceByNameOrThrow($this->daedalus->getPlanetPlace()->getName())
                ->hasEquipmentByName(GameRationEnum::ALIEN_STEAK)
        );

        // then the icarus should not be on the planet
        $I->assertFalse(
            $this->daedalus
                ->getPlaceByNameOrThrow($this->daedalus->getPlanetPlace()->getName())
                ->hasEquipmentByName(EquipmentEnum::ICARUS)
        );
    }

    public function closeExplorationShouldNotReturnPlanetEquipmentInDaedalusIfEveryoneIsLost(FunctionalTester $I): void
    {
        // given a planet with 1 desert sector
        $planet = $this->createPlanet([PlanetSectorEnum::DESERT], $I);

        // given Chun has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $planet,
            explorators: new PlayerCollection([$this->chun]),
        );

        // given I have some steaks on the planet
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::ALIEN_STEAK,
            equipmentHolder: $this->daedalus->getPlanetPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given Chun is lost
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        // when exploration is closed
        $this->explorationService->closeExploration($exploration, ['test']);

        // then I should see not Icarus in Icarus Bay
        $I->assertFalse(
            $this->daedalus
                ->getPlaceByNameOrThrow(RoomEnum::ICARUS_BAY)
                ->hasEquipmentByName(EquipmentEnum::ICARUS)
        );
    }

    public function closeExplorationShouldNotGetPlayerDirtyWithIcarusLavatoryProject(FunctionalTester $I): void
    {
        // given Icarus Lavatory project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::ICARUS_LAVATORY),
            author: $this->player,
            I: $I,
        );

        // given a planet with 1 desert sector
        $planet = $this->createPlanet([PlanetSectorEnum::DESERT], $I);

        // given Chun has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $planet,
            explorators: new PlayerCollection([$this->chun]),
        );

        // when exploration is closed
        $this->explorationService->closeExploration($exploration, []);

        // then Chun should not be dirty
        $I->assertFalse($this->chun->hasStatus(PlayerStatusEnum::DIRTY));
    }

    #[DataProvider('skillExplorationCycleIncrementDataProvider')]
    public function ensureSkillAddPlusOneCycleIfPresent(FunctionalTester $I, Example $scenario): void
    {
        $planet = $this->createPlanet([PlanetSectorEnum::DESERT], $I);
        $roland = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::ROLAND);
        $this->addSkillToPlayer($scenario[0], $I, $roland);

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $planet,
            explorators: new PlayerCollection([$roland]),
        );

        $I->assertSame($exploration->getNumberOfSectionsToVisit(), $scenario[1]);
    }

    public function closeExplorationShouldDeleteNonIcarusShipEvenWithAutoReturnProject(FunctionalTester $I): void
    {
        // given Auto Return Icarus project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::AUTO_RETURN_ICARUS),
            author: $this->player,
            I: $I,
        );

        // given a planet with 1 desert sector
        $planet = $this->createPlanet([PlanetSectorEnum::DESERT], $I);

        // given there is a non-Icarus ship on the planet
        $patrolShip = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PATROL_SHIP,
            equipmentHolder: $this->daedalus->getPlanetPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given Chun is in Icarus Bay
        $this->chun->changePlace($this->icarus->getPlace());

        // given an exploration is created with the non-Icarus ship
        $exploration = $this->explorationService->createExploration(
            players: new PlayerCollection([$this->chun]),
            explorationShip: $patrolShip,
            numberOfSectorsToVisit: $planet->getSize(),
            reasons: ['test'],
        );

        // given Chun dies
        $this->playerService->killPlayer(
            player: $this->chun,
            endReason: EndCauseEnum::INJURY,
            time: new \DateTime(),
        );

        // when exploration is closed
        $this->explorationService->closeExploration($exploration, ['test']);

        // then the non-Icarus ship should be deleted
        $I->assertFalse(
            $this->daedalus
                ->getPlaceByNameOrThrow($this->daedalus->getPlanetPlace()->getName())
                ->hasEquipmentByName(EquipmentEnum::PATROL_SHIP)
        );
    }

    private function skillExplorationCycleIncrementDataProvider(): iterable
    {
        yield 'Skill: Sprinter' => [SkillEnum::SPRINTER, 10];

        yield 'Skill: Pilot' => [SkillEnum::PILOT, 9];

        yield 'Skill: Optimist' => [SkillEnum::OPTIMIST, 9];

        yield 'Skill: Shooter' => [SkillEnum::SHOOTER, 9];
    }
}
