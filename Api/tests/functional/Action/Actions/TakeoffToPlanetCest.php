<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\TakeoffToPlanet;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Exploration\Entity\ClosedExploration;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetName;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TakeoffToPlanetCest extends AbstractFunctionalTest
{
    private Action $takeoffToPlanetConfig;
    private TakeoffToPlanet $takeoffToPlanetAction;

    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    private GameEquipment $icarus;
    private Planet $planet;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->takeoffToPlanetConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::TAKEOFF_TO_PLANET]);
        $this->takeoffToPlanetAction = $I->grabService(TakeoffToPlanet::class);

        $this->eventService = $I->grabService(EventServiceInterface::class);
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

        $this->createPlanetForTest($I);
    }

    public function testTakeoffToPlanetNotVisibleIfDaedalusIsNotInOrbit(FunctionalTester $I): void
    {
        // given Daedalus is not in orbit
        $this->statusService->removeStatus(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );

        // when player tries to takeoff to planet
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player, $this->icarus);

        // then the action is visible
        $I->assertFalse($this->takeoffToPlanetAction->isVisible());
    }

    public function testTakeoffToPlanetNotExectableIfDaedalusIsTraveling(FunctionalTester $I): void
    {
        // given Daedalus is traveling
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::TRAVELING,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );

        // when player tries to takeoff to planet
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player, $this->icarus);

        // then the action is not executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::DAEDALUS_TRAVELING,
            actual: $this->takeoffToPlanetAction->cannotExecuteReason(),
        );
    }

    public function testTakeoffToPlanetNotExectableIfAllPlanetSectorsHasBeenVisited(FunctionalTester $I): void
    {
        // given all planet sectors have been visited
        $planetSectors = $this->planet->getSectors()->map(static fn (PlanetSector $sector) => $sector->visit());
        $this->planet->setSectors($planetSectors);
        $I->haveInRepository($this->planet);

        // when player tries to takeoff to planet
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player, $this->icarus);

        // then the action is not executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::EXPLORE_NOTHING_LEFT,
            actual: $this->takeoffToPlanetAction->cannotExecuteReason(),
        );
    }

    public function testTakeoffToPlanetNotExectableIfAnExplorationIsOnGoing(FunctionalTester $I): void
    {
        // given players have spacesuit in their inventory to explore oxygen-free planets
        $spacesuitConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => GearItemEnum::SPACESUIT]);
        $spacesuit = new GameItem($this->player1);
        $spacesuit
            ->setName(GearItemEnum::SPACESUIT)
            ->setEquipment($spacesuitConfig);
        $I->haveInRepository($spacesuit);
        $spacesuit2 = new GameItem($this->player2);
        $spacesuit2
            ->setName(GearItemEnum::SPACESUIT)
            ->setEquipment($spacesuitConfig);
        $I->haveInRepository($spacesuit2);

        // given players are exploring the planet
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player, $this->icarus);
        $this->takeoffToPlanetAction->execute();

        // given a new player is in a patrol ship
        $patrolShipAlphaTamarinPlace = $this->createExtraPlace(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN, $I, $this->daedalus);
        $patrolShipConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN]);
        $patrolShip = new GameEquipment($patrolShipAlphaTamarinPlace);
        $patrolShip
            ->setName(EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN)
            ->setEquipment($patrolShipConfig);
        $I->haveInRepository($patrolShip);

        $newPlayer = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
        $newPlayer->changePlace($patrolShipAlphaTamarinPlace);

        // when this new player tries to takeoff to planet
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $newPlayer, $patrolShip);

        // then the action is not executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::EXPLORATION_ALREADY_ONGOING,
            actual: $this->takeoffToPlanetAction->cannotExecuteReason(),
        );
    }

    public function testTakeoffToPlanetSuccessCreatesExplorationEntity(FunctionalTester $I): void
    {
        // given players have spacesuit in their inventory to explore oxygen-free planets
        $spacesuitConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => GearItemEnum::SPACESUIT]);
        $spacesuit = new GameItem($this->player1);
        $spacesuit
            ->setName(GearItemEnum::SPACESUIT)
            ->setEquipment($spacesuitConfig);
        $I->haveInRepository($spacesuit);
        $spacesuit2 = new GameItem($this->player2);
        $spacesuit2
            ->setName(GearItemEnum::SPACESUIT)
            ->setEquipment($spacesuitConfig);
        $I->haveInRepository($spacesuit2);

        // given there is no exploration entity
        $I->dontSeeInRepository(Exploration::class, ['planet' => $this->planet]);

        // when player tries to takeoff to planet
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player, $this->icarus);
        $this->takeoffToPlanetAction->execute();

        // then an exploration entity is created
        $I->seeInRepository(Exploration::class, ['planet' => $this->planet]);
    }

    public function testTakeoffToPlanetSuccessMoveIcarusBayPlayersToPlanetPlace(FunctionalTester $I): void
    {
        // given player1 and player2 are in Icarus Bay
        $I->assertEquals(
            expected: $this->icarus->getPlace(),
            actual: $this->player1->getPlace(),
        );
        $I->assertEquals(
            expected: $this->icarus->getPlace(),
            actual: $this->player2->getPlace(),
        );

        // given players have spacesuit in their inventory to explore oxygen-free planets
        $spacesuitConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => GearItemEnum::SPACESUIT]);
        $spacesuit = new GameItem($this->player1);
        $spacesuit
            ->setName(GearItemEnum::SPACESUIT)
            ->setEquipment($spacesuitConfig);
        $I->haveInRepository($spacesuit);
        $spacesuit2 = new GameItem($this->player2);
        $spacesuit2
            ->setName(GearItemEnum::SPACESUIT)
            ->setEquipment($spacesuitConfig);
        $I->haveInRepository($spacesuit2);

        // when player tries to takeoff to planet
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player, $this->icarus);
        $this->takeoffToPlanetAction->execute();

        // then player1 and player2 are in the planet place
        $I->assertEquals(
            expected: $this->daedalus->getPlanetPlace(),
            actual: $this->player1->getPlace(),
        );
        $I->assertEquals(
            expected: $this->daedalus->getPlanetPlace(),
            actual: $this->player2->getPlace(),
        );
    }

    public function testTakeoffToPlaneSuccesstMoveIcarusToPlanetPlace(FunctionalTester $I): void
    {
        // given icarus ship is in Icarus Bay
        $I->assertEquals(
            expected: $this->daedalus->getPlaceByName(RoomEnum::ICARUS_BAY),
            actual: $this->icarus->getPlace(),
        );

        // given players have spacesuit in their inventory to explore oxygen-free planets
        $spacesuitConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => GearItemEnum::SPACESUIT]);
        $spacesuit = new GameItem($this->player1);
        $spacesuit
            ->setName(GearItemEnum::SPACESUIT)
            ->setEquipment($spacesuitConfig);
        $I->haveInRepository($spacesuit);
        $spacesuit2 = new GameItem($this->player2);
        $spacesuit2
            ->setName(GearItemEnum::SPACESUIT)
            ->setEquipment($spacesuitConfig);
        $I->haveInRepository($spacesuit2);

        // when player tries to takeoff to planet
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player, $this->icarus);
        $this->takeoffToPlanetAction->execute();

        // then icarus ship is in the planet place
        $I->assertEquals(
            expected: $this->daedalus->getPlanetPlace(),
            actual: $this->icarus->getPlace(),
        );
    }

    public function testTakeOffToPlanetSuccessTriggersLandingEvent(FunctionalTester $I): void
    {
        // given players have spacesuit in their inventory to explore oxygen-free planets
        $spacesuitConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => GearItemEnum::SPACESUIT]);
        $spacesuit = new GameItem($this->player1);
        $spacesuit
            ->setName(GearItemEnum::SPACESUIT)
            ->setEquipment($spacesuitConfig);
        $I->haveInRepository($spacesuit);
        $spacesuit2 = new GameItem($this->player2);
        $spacesuit2
            ->setName(GearItemEnum::SPACESUIT)
            ->setEquipment($spacesuitConfig);
        $I->haveInRepository($spacesuit2);

        // when player tries to takeoff to planet
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player, $this->icarus);
        $this->takeoffToPlanetAction->execute();

        // then landing event is dispatched
        $I->seeInRepository(
            entity: ExplorationLog::class,
            params: [
                'planetSectorName' => PlanetSectorEnum::LANDING,
            ]
        );
    }

    public function testTakeOffToPlanetSucessWithoutSpaceSuitOnOxygenFreePlanetDoNotCreateExplorationEntities(FunctionalTester $I): void
    {
        // given players do not have spacesuit in their inventory

        // when player tries to takeoff to planet
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player, $this->icarus);
        $this->takeoffToPlanetAction->execute();

        // then an exploration entity is not created
        $I->dontSeeInRepository(Exploration::class, ['planet' => $this->planet]);
        // then a closed exploration entity is not created
        $I->dontSeeInRepository(ClosedExploration::class);
    }

    public function testTakeOffToPlanetSucessWithoutSpaceSuitOnOxygenFreePlanetCreatesASpecificLog(FunctionalTester $I): void
    {
        // given players do not have spacesuit in their inventory

        // when player tries to takeoff to planet
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player, $this->icarus);
        $this->takeoffToPlanetAction->execute();

        // then a specific log is created
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => RoomEnum::ICARUS_BAY,
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'log' => LogEnum::ALL_EXPLORATORS_STUCKED,
                'visibility' => VisibilityEnum::PRIVATE,
            ]
        );
    }

    public function testTakeOffToPlanetSuccessCreatesStuckedInTheShipStatus(FunctionalTester $I): void
    {
        // given player 1 has a spacesuit in their inventory but player 2 does not
        $spacesuitConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => GearItemEnum::SPACESUIT]);
        $spacesuit = new GameItem($this->player1);
        $spacesuit
            ->setName(GearItemEnum::SPACESUIT)
            ->setEquipment($spacesuitConfig);
        $I->haveInRepository($spacesuit);

        // when player tries to takeoff to planet
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player1, $this->icarus);
        $this->takeoffToPlanetAction->execute();

        // then player 2 has a stucked in the ship status but player 1 does not
        $I->assertTrue($this->player2->hasStatus(PlayerStatusEnum::STUCK_IN_THE_SHIP));
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => RoomEnum::PLANET,
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'log' => StatusEventLogEnum::STUCK_IN_THE_SHIP,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
        $I->assertFalse($this->player1->hasStatus(PlayerStatusEnum::STUCK_IN_THE_SHIP));
    }

    public function testTakeoffToPlanetSuccessCreatesAPublicLog(FunctionalTester $I): void
    {
        // given players have spacesuit in their inventory to explore oxygen-free planets
        $spacesuitConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => GearItemEnum::SPACESUIT]);
        $spacesuit = new GameItem($this->player1);
        $spacesuit
            ->setName(GearItemEnum::SPACESUIT)
            ->setEquipment($spacesuitConfig);
        $I->haveInRepository($spacesuit);
        $spacesuit2 = new GameItem($this->player2);
        $spacesuit2
            ->setName(GearItemEnum::SPACESUIT)
            ->setEquipment($spacesuitConfig);
        $I->haveInRepository($spacesuit2);

        // when player tries to takeoff to planet
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player, $this->icarus);
        $this->takeoffToPlanetAction->execute();

        // then a public log should be created in Icarus Bay
        /** @var RoomLog $log */
        $log = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => 'icarus_bay',
                'log' => ActionLogEnum::TAKEOFF_TO_PLANET_SUCCESS,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );

        // then the log should contain the right parameters
        $I->assertEquals(
            expected: [
                'character' => $this->player->getLogName(),
                'target_equipment' => $this->icarus->getLogName(),
            ],
            actual: $log->getParameters(),
        );
    }

    public function testISeePreviousExplorationLogsOnTheSamePlanet(FunctionalTester $I): void
    {
        // given players launch a first exploration without spacesuit
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player1, $this->icarus);
        $this->takeoffToPlanetAction->execute();

        // given this prints some logs in planet place
        /** @var array<int, RoomLog> $logs */
        $logs = $I->grabEntitiesFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => RoomEnum::PLANET,
                'log' => PlayerStatusEnum::STUCK_IN_THE_SHIP,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );

        // when players launch a second exploration without spacesuit
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player1, $this->icarus);
        $this->takeoffToPlanetAction->execute();

        // then the previous logs are still visible
        foreach ($logs as $log) {
            $I->assertTrue($log->getVisibility() === VisibilityEnum::PUBLIC);
        }
    }

    public function testIDontSeePreviousExplorationLogsOnANewPlanet(FunctionalTester $I): void
    {
        // given players launch a first exploration without spacesuit
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player1, $this->icarus);
        $this->takeoffToPlanetAction->execute();

        // given this prints some logs in planet place
        /** @var array<int, RoomLog> $logs */
        $logs = $I->grabEntitiesFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => RoomEnum::PLANET,
                'log' => PlayerStatusEnum::STUCK_IN_THE_SHIP,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );

        // given players leave orbit
        $daedalusEvent = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [ActionEnum::LEAVE_ORBIT],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_LAUNCHED);

        // given there is a new planet to explore
        $this->createPlanetForTest($I);

        // when players launch a second exploration without spacesuit
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player1, $this->icarus);
        $this->takeoffToPlanetAction->execute();

        // then the previous logs are not visible
        foreach ($logs as $log) {
            $I->assertTrue($log->getVisibility() !== VisibilityEnum::PUBLIC);
        }
    }

    private function createPlanetForTest(FunctionalTester $I): void
    {
        // given a planet without oxygen has been found
        $planetName = new PlanetName();
        $planetName->setFirstSyllable(1);
        $planetName->setFourthSyllable(1);
        $I->haveInRepository($planetName);

        $this->planet = new Planet($this->player);
        $this->planet
            ->setName($planetName)
            ->setSize(1);
        $I->haveInRepository($this->planet);

        $desertSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::DESERT . '_default']);
        $sector = new PlanetSector($desertSectorConfig, $this->planet);
        $I->haveInRepository($sector);

        $this->planet->setSectors(new ArrayCollection([$sector]));

        // given the Daedalus is in orbit around the planet
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
    }
}
