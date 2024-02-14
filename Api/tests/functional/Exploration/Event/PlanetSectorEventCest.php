<?php

declare(strict_types=1);

namespace Mush\Tests\Exploration\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class PlanetSectorEventCest extends AbstractExplorationTester
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given explorators have a spacesuit
        foreach ($this->players as $player) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: GearItemEnum::SPACESUIT,
                equipmentHolder: $player,
                reasons: [],
                time: new \DateTime(),
            );
        }
    }

    public function testAccidentHurtsExplorator(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::SISMIC_ACTIVITY], $I),
            explorators: $this->players
        );

        // given there is a sismic sector on the planet with accident event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::SISMIC_ACTIVITY,
            events: [PlanetSectorEvent::ACCIDENT_3_5 => 1]
        );

        $player1HealthBeforeEvent = $this->player->getHealthPoint();

        // when accident event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then one of the explorators health is decreased
        if ($this->player->getHealthPoint() === $player1HealthBeforeEvent) {
            $I->assertLessThan(
                expected: $this->player2->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
                actual: $this->player2->getHealthPoint(),
            );
        } else {
            $I->assertLessThan(
                expected: $this->player->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
                actual: $this->player->getHealthPoint(),
            );
        }
    }

    public function testDisasterHurtsAllExplorators(FunctionalTester $I): void
    {
        // given only disaster event can happen in landing sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::LANDING,
            events: [PlanetSectorEvent::DISASTER_3_5 => 1]
        );

        // when an exploration is created, the disaster event is dispatched
        $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::LANDING], $I),
            explorators: $this->players
        );

        // then players health is decreased
        foreach ($this->players as $player) {
            $I->assertLessThan(
                expected: $player->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
                actual: $player->getHealthPoint(),
            );
        }
    }

    public function testTiredHurtsAllExplorators(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::DESERT], $I),
            explorators: $this->players
        );

        // given there is a desert sector on the planet with tired event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::DESERT,
            events: [PlanetSectorEvent::TIRED_2 => 1]
        );

        $player1HealthBeforeEvent = $this->player->getHealthPoint();
        $player2HealthBeforeEvent = $this->player2->getHealthPoint();

        // when tired event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then players health is decreased
        $I->assertEquals(
            expected: $player1HealthBeforeEvent - 2,
            actual: $this->player->getHealthPoint(),
        );
        $I->assertEquals(
            expected: $player2HealthBeforeEvent - 2,
            actual: $this->player2->getHealthPoint(),
        );
    }

    public function testOxygenEventCreatesOxygenStatus(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players
        );

        // given there is only oxygen event in oxygen sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::OXYGEN,
            events: [PlanetSectorEvent::OXYGEN_24 => 1]
        );

        // when oxygen event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then daedalus has an oxygen status
        /** @var ChargeStatus $daedalusOxygenStatus */
        $daedalusOxygenStatus = $this->daedalus->getStatusByName(DaedalusStatusEnum::EXPLORATION_OXYGEN);
        $I->assertEquals(24, $daedalusOxygenStatus?->getCharge());
    }

    public function testFuelEventCreatesFuelStatus(FunctionalTester $I): void
    {
        // given there is only fuel event in fuel sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::HYDROCARBON,
            events: [PlanetSectorEvent::FUEL_6 => 1]
        );

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::HYDROCARBON], $I),
            explorators: $this->players
        );

        // when fuel event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then daedalus has an fuel status
        /** @var ChargeStatus $daedalusFuelStatus */
        $daedalusFuelStatus = $this->daedalus->getStatusByName(DaedalusStatusEnum::EXPLORATION_FUEL);
        $I->assertEquals(6, $daedalusFuelStatus?->getCharge());
    }

    public function testArtefactEventCreatesAnArtefactInPlanetPlace(FunctionalTester $I): void
    {
        // given exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: new ArrayCollection([$this->player])
        );

        // given only artefact event can happen in intelligent sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            events: [PlanetSectorEvent::ARTEFACT => 1]
        );

        // when artefact event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then one artefact is created in the planet place
        $planetPlaceEquipments = $this->daedalus
            ->getPlanetPlace()
            ->getEquipments()
            ->map(fn (GameEquipment $gameEquipment) => $gameEquipment->getLogName())
            ->toArray()
        ;
        $I->assertNotEmpty(array_intersect($planetPlaceEquipments, ItemEnum::getArtefacts()->toArray()));

        // then I should see a public log in planet place to tell an explorator has found an artefact
        /** @var RoomLog $roomLog */
        $roomLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlanetPlace()->getLogName(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::FOUND_ITEM_IN_EXPLORATION,
            ]
        );

        // then the log should be properly parameterized
        $player = $roomLog->getParameters()['character'];
        $I->assertEquals($this->player->getLogName(), $player);

        $artefact = $roomLog->getParameters()['target_item'];
        $I->assertTrue(in_array($artefact, $planetPlaceEquipments));
    }

    public function testKillRandomEventKillsOneExplorator(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::SISMIC_ACTIVITY], $I),
            explorators: $this->players
        );

        // given only kill random event can happen in sismic sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::SISMIC_ACTIVITY,
            events: [PlanetSectorEvent::KILL_RANDOM => 1]
        );

        // when kill random event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then one of the explorators is dead
        if ($this->player->isAlive()) {
            $I->assertFalse($this->player2->isAlive());
            $deadPlayer = $this->player2;
        } else {
            $I->assertFalse($this->player->isAlive());
            $deadPlayer = $this->player;
        }

        // then I see a public death log with the "exploration" cause
        $deathLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlanetPlace()->getLogName(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::DEATH,
            ]
        );
        $deathLogParameters = $deathLog->getParameters();
        $I->assertEquals($deadPlayer->getLogName(), $deathLogParameters['target_character']);
        $I->assertEquals(EndCauseEnum::EXPLORATION, $deathLogParameters['end_cause']);
    }

    public function testKillRandomEventDoesNotKillLostExplorator(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::SISMIC_ACTIVITY], $I),
            explorators: $this->players
        );

        // given only kill random event can happen in sismic sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::SISMIC_ACTIVITY,
            events: [PlanetSectorEvent::KILL_RANDOM => 1]
        );

        // given one player2 is lost
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $this->player2,
            tags: [],
            time: new \DateTime(),
        );

        // when kill random event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then it's player1 who dies
        $I->assertFalse($this->player->isAlive());
    }

    public function testKillRandomEventDoesNotKillStuckInShipExplorators(FunctionalTester $I): void
    {
        // given player2 does not have a spacesuit so they will be stuck in the ship
        $this->gameEquipmentService->delete($this->player2->getEquipmentByName(GearItemEnum::SPACESUIT));

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::SISMIC_ACTIVITY], $I),
            explorators: $this->players
        );

        // given only kill random event can happen in sismic sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::SISMIC_ACTIVITY,
            events: [PlanetSectorEvent::KILL_RANDOM => 1]
        );

        // when kill random event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then it's player1 who dies
        $I->assertFalse($this->player->isAlive());
    }

    public function testKillAllEventKillsAllExplorators(FunctionalTester $I): void
    {
        // given some extra explorators
        $derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
        $janice = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JANICE);
        $this->players->add($derek);
        $this->players->add($janice);

        // given Janice has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $janice,
            reasons: [],
            time: new \DateTime(),
        );

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::VOLCANIC_ACTIVITY], $I),
            explorators: $this->players
        );

        // given Janice is lost
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $janice,
            tags: [],
            time: new \DateTime(),
        );

        // given only kill all event can happen in volcanoes sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::VOLCANIC_ACTIVITY,
            events: [PlanetSectorEvent::KILL_ALL => 1]
        );

        // when kill all event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then player 1 and player 2 are dead
        $I->assertFalse($this->player->isAlive());
        $I->assertFalse($this->player2->isAlive());

        // then Janice is alive, because she is lost
        $I->assertTrue($janice->isAlive());

        // then Derek is alive, because he is stuck in the ship (no spacesuit)
        $I->assertTrue($derek->isAlive());
    }

    public function testFightEventRemovesHealthToAllExplorators(FunctionalTester $I): void
    {
        // given some extra explorators
        $derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
        $janice = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JANICE);
        $this->players->add($derek);
        $this->players->add($janice);

        // given Janice has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $janice,
            reasons: [],
            time: new \DateTime(),
        );

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: $this->players
        );

        // given Janice is lost
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $janice,
            tags: [],
            time: new \DateTime(),
        );

        // given only fight event can happen in intelligent sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            events: ['fight_12' => 1]
        );

        $playersHealthBeforeEvent = [];
        foreach ($this->players as $player) {
            $playersHealthBeforeEvent[$player->getLogName()] = $player->getHealthPoint();
        }

        // when fight is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then player1 and player2 have their health decreased
        foreach ([$this->player, $this->player2] as $player) {
            $I->assertLessThan(
                expected: $playersHealthBeforeEvent[$player->getLogName()],
                actual: $player->getHealthPoint(),
            );
        }

        // then Janice still has the same health, as she is lost
        $I->assertEquals(
            expected: $playersHealthBeforeEvent[$janice->getLogName()],
            actual: $janice->getHealthPoint(),
        );

        // then Derek still has the same health, as he is stuck in the ship (no spacesuit)
        $I->assertEquals(
            expected: $playersHealthBeforeEvent[$derek->getLogName()],
            actual: $derek->getHealthPoint(),
        );
    }

    public function testFightEventDoesNotRemoveHealthToExploratorsIfTheyHaveEnoughStrength(FunctionalTester $I): void
    {
        // given some extra explorators
        $derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
        $janice = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JANICE);
        $this->players->add($derek);
        $this->players->add($janice);

        // given Janice has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $janice,
            reasons: [],
            time: new \DateTime(),
        );

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: $this->players
        );

        // given Janice is lost
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $janice,
            tags: [],
            time: new \DateTime(),
        );

        // given only fight event can happen in intelligent sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            events: ['fight_1' => 1]
        );

        $playersHealthBeforeEvent = [];
        foreach ($this->players as $player) {
            $playersHealthBeforeEvent[$player->getLogName()] = $player->getHealthPoint();
        }

        // when fight is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then all players have the same health as before the event becausse they killed the monster
        foreach ($this->players as $player) {
            $I->assertEquals(
                expected: $playersHealthBeforeEvent[$player->getLogName()],
                actual: $player->getHealthPoint(),
            );
        }
    }
}
