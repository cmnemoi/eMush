<?php

declare(strict_types=1);

namespace Mush\Tests\Exploration\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
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

    private Player $chun;
    private Player $kuanTi;
    private Player $derek;
    private Player $janice;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given our explorators are Chun, Kuan-Ti, Derek, and Janice
        $this->chun = $this->player;
        $this->kuanTi = $this->player2;
        $this->derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
        $this->janice = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JANICE);
        $this->players->add($this->derek);
        $this->players->add($this->janice);

        // given Chun, Kuan-Ti, and Janice have a spacesuit
        foreach ([$this->chun, $this->kuanTi, $this->janice] as $player) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: GearItemEnum::SPACESUIT,
                equipmentHolder: $player,
                reasons: [],
                time: new \DateTime(),
            );
        }

        // given Janice is lost
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $this->janice,
            tags: [],
            time: new \DateTime(),
        );

        // given lost sector has no chance to be visited
        $lostSectorConfig = $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::LOST,
            events: []
        );
        $lostSectorConfig->setWeightAtPlanetExploration(0);
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

        $chunHealthBeforeEvent = $this->chun->getHealthPoint();
        $kuanTiHealthBeforeEvent = $this->kuanTi->getHealthPoint();
        $janiceHealthBeforeEvent = $this->janice->getHealthPoint();
        $derekHealthBeforeEvent = $this->derek->getHealthPoint();

        // when accident event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then Chun or Kuan-Ti health is decreased
        if ($this->chun->getHealthPoint() === $chunHealthBeforeEvent) {
            $I->assertLessThan(
                expected: $kuanTiHealthBeforeEvent,
                actual: $this->kuanTi->getHealthPoint(),
            );
        } else {
            $I->assertLessThan(
                expected: $chunHealthBeforeEvent,
                actual: $this->chun->getHealthPoint(),
            );
        }

        // then Janice still has the same health, as she is lost
        $I->assertEquals(
            expected: $janiceHealthBeforeEvent,
            actual: $this->janice->getHealthPoint(),
        );

        // then Derek still has the same health, as he is stuck in the ship (no spacesuit)
        $I->assertEquals(
            expected: $derekHealthBeforeEvent,
            actual: $this->derek->getHealthPoint(),
        );
    }

    public function testAccidentKillsPlayerWithTheRightDeathCause(FunctionalTester $I): void
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

        // given player1 and player2 have 1 health point
        $this->player->setHealthPoint(1);
        $this->player2->setHealthPoint(1);

        // when accident event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then one of the player is dead
        if ($this->player->isAlive()) {
            $deadPlayer = $this->player2;
            $I->assertFalse($deadPlayer->isAlive());
        } else {
            $deadPlayer = $this->player;
            $I->assertFalse($deadPlayer->isAlive());
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

        // then all explorators health is decreased, even if lost or stuck in the ship
        foreach ($this->players as $player) {
            $I->assertLessThan(
                expected: $player->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
                actual: $player->getHealthPoint(),
            );
        }
    }

    public function testDisasterKillsPlayerWithTheRightDeathCause(FunctionalTester $I): void
    {
        // given only disaster event can happen in landing sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::LANDING,
            events: [PlanetSectorEvent::DISASTER_3_5 => 1]
        );

        // given player1 has 1 health point
        $this->player->setHealthPoint(1);

        // when an exploration is created, the disaster event is dispatched
        $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::LANDING], $I),
            explorators: $this->players
        );

        // then player1 is dead
        $I->assertFalse($this->player->isAlive());

        // then I see 1 public death log with the "exploration" cause
        $deathLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlanetPlace()->getLogName(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::DEATH,
            ]
        );
        $deathLogParameters = $deathLog->getParameters();
        $I->assertEquals($this->player->getLogName(), $deathLogParameters['target_character']);
        $I->assertEquals(EndCauseEnum::EXPLORATION, $deathLogParameters['end_cause']);
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

        $chunHealthBeforeEvent = $this->chun->getHealthPoint();
        $kuanTiHealthBeforeEvent = $this->kuanTi->getHealthPoint();
        $janiceHealthBeforeEvent = $this->janice->getHealthPoint();
        $derekHealthBeforeEvent = $this->derek->getHealthPoint();

        // when tired event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then Chun and Kuan-Ti health is decreased
        $I->assertEquals(
            expected: $chunHealthBeforeEvent - 2,
            actual: $this->player->getHealthPoint(),
        );
        $I->assertEquals(
            expected: $kuanTiHealthBeforeEvent - 2,
            actual: $this->player2->getHealthPoint(),
        );

        // then Janice still has the same health, as she is lost
        $I->assertEquals(
            expected: $janiceHealthBeforeEvent,
            actual: $this->janice->getHealthPoint(),
        );

        // then Derek still has the same health, as he is stuck in the ship (no spacesuit)
        $I->assertEquals(
            expected: $derekHealthBeforeEvent,
            actual: $this->derek->getHealthPoint(),
        );
    }

    public function testTiredKillsPlayerWithTheRightDeathCause(FunctionalTester $I): void
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

        // given player1 and player2 have 2 health point
        $this->player->setHealthPoint(2);
        $this->player2->setHealthPoint(2);

        // when tired event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then players are dead

        // then I see two public death logs with the "exploration" cause
        $deathLogs = $I->grabEntitiesFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlanetPlace()->getLogName(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::DEATH,
            ]
        );
        $I->assertCount(2, $deathLogs);

        foreach ($deathLogs as $deathLog) {
            $deathLogParameters = $deathLog->getParameters();
            $I->assertEquals(EndCauseEnum::EXPLORATION, $deathLogParameters['end_cause']);
        }
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

        // then Janice is alive, because she is lost
        $I->assertTrue($this->janice->isAlive());

        // then Derek is alive, because he is stuck in the ship (no spacesuit)
        $I->assertTrue($this->derek->isAlive());

        // then Chun or Kuan-Ti is dead
        if ($this->chun->isAlive()) {
            $I->assertFalse($this->kuanTi->isAlive());
            $deadPlayer = $this->kuanTi;
        } else {
            $I->assertFalse($this->chun->isAlive());
            $deadPlayer = $this->chun;
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

    public function testKillAllEventKillsAllExplorators(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::VOLCANIC_ACTIVITY], $I),
            explorators: $this->players
        );

        // given only kill all event can happen in volcanoes sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::VOLCANIC_ACTIVITY,
            events: [PlanetSectorEvent::KILL_ALL => 1]
        );

        // when kill all event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then Chun and Kuan-Ti are dead
        $I->assertFalse($this->chun->isAlive());
        $I->assertFalse($this->kuanTi->isAlive());

        // then I see two public death logs with the "exploration" cause
        $deathLogs = $I->grabEntitiesFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlanetPlace()->getLogName(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::DEATH,
            ]
        );
        $I->assertCount(2, $deathLogs);
        foreach ($deathLogs as $deathLog) {
            $deathLogParameters = $deathLog->getParameters();
            $I->assertEquals(EndCauseEnum::EXPLORATION, $deathLogParameters['end_cause']);
        }

        // then Janice is alive, because she is lost
        $I->assertTrue($this->janice->isAlive());

        // then Derek is alive, because he is stuck in the ship (no spacesuit)
        $I->assertTrue($this->derek->isAlive());
    }

    public function testFightEventRemovesHealthToAllExplorators(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: $this->players
        );

        // given Janice is lost
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $this->janice,
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
            expected: $playersHealthBeforeEvent[$this->janice->getLogName()],
            actual: $this->janice->getHealthPoint(),
        );

        // then Derek still has the same health, as he is stuck in the ship (no spacesuit)
        $I->assertEquals(
            expected: $playersHealthBeforeEvent[$this->derek->getLogName()],
            actual: $this->derek->getHealthPoint(),
        );
    }

    public function testFightEventDoesNotRemoveHealthToExploratorsIfTheyHaveEnoughStrength(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: $this->players
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

        // then all players have the same health as before the event because they killed the monster
        foreach ($this->players as $player) {
            $I->assertEquals(
                expected: $playersHealthBeforeEvent[$player->getLogName()],
                actual: $player->getHealthPoint(),
            );
        }
    }

    public function testProvisionEvent(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::RUMINANT], $I),
            explorators: $this->players
        );

        // given only provision event can happen in ruminant sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::RUMINANT,
            events: [PlanetSectorEvent::PROVISION_4 => 1]
        );

        // when provision event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then I should see 4 alien steaks in planet place
        $I->assertCount(4, $this->daedalus->getPlanetPlace()->getEquipments()->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === GameRationEnum::ALIEN_STEAK));

        // then I should see 4 public logs in planet place to tell an explorator has found an alien steak
        $roomLogs = $I->grabEntitiesFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlanetPlace()->getLogName(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::FOUND_ITEM_IN_EXPLORATION,
            ]
        );
        $I->assertCount(4, $roomLogs);
        $roomLogParameters = $roomLogs[0]->getParameters();
        $I->assertEquals(GameRationEnum::ALIEN_STEAK, $roomLogParameters['target_item']);

        // then the founder should be Chun or Kuan-Ti (not Janice or Derek - lost or stuck in ship)
        $I->assertTrue(in_array($roomLogParameters['character'], [$this->chun->getLogName(), $this->kuanTi->getLogName()]));
    }

    public function testFindLostEvent(FunctionalTester $I): void
    {
        // given Janice has 10 morale points
        $this->janice->setMoralPoint(10);

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([], $I),
            explorators: $this->players
        );

        // given only find lost event can happen in lost sector
        $lostSectorConfig = $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::LOST,
            events: [PlanetSectorEvent::FIND_LOST => 1]
        );
        $lostSectorConfig->setWeightAtPlanetExploration(1);

        // when find lost event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then Janice is not lost anymore
        $I->assertFalse($this->janice->hasStatus(PlayerStatusEnum::LOST));

        // then Janice should have gained 3 morale points
        $I->assertEquals(
            expected: 13,
            actual: $this->janice->getMoralPoint(),
        );

        // then I should see a private log in planet place to tell that Janice has gained morale points
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlanetPlace()->getLogName(),
                'playerInfo' => $this->janice->getPlayerInfo(),
                'visibility' => VisibilityEnum::PRIVATE,
                'log' => PlayerModifierLogEnum::GAIN_MORAL_POINT,
            ]
        );
    }

    public function testKillLostEvent(FunctionalTester $I): void
    {
        // given an exploration is created without Janice
        $exploration = $this->createExploration(
            planet: $this->createPlanet([], $I),
            explorators: new ArrayCollection([$this->chun, $this->kuanTi, $this->derek])
        );

        // given only kill lost event can happen in lost sector
        $lostSectorConfig = $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::LOST,
            events: [PlanetSectorEvent::KILL_LOST => 1]
        );
        $lostSectorConfig->setWeightAtPlanetExploration(1);

        // when kill lost event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then Janice is dead
        $I->assertFalse($this->janice->isAlive());

        // then death cause should be "exploration_lost"
        $I->assertEquals(
            expected: EndCauseEnum::EXPLORATION_LOST,
            actual: $this->janice->getPlayerInfo()->getClosedPlayer()->getEndCause(),
        );
    }
}
