<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Exploration\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Enum\AlertEnum;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;
use Mush\Triumph\Enum\TriumphEnum;

final class PlanetSectorEventCest extends AbstractExplorationTester
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private StatusServiceInterface $statusService;
    private Player $derek;
    private Player $janice;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
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
        $this->givenLostPlayer($this->janice);
    }

    public function testAccidentHurtsExplorator(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::SEISMIC_ACTIVITY], $I),
            explorators: $this->players
        );

        // given there is a seismic sector on the planet with accident event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::SEISMIC_ACTIVITY,
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
            planet: $this->createPlanet([PlanetSectorEnum::SEISMIC_ACTIVITY], $I),
            explorators: $this->players
        );

        // given there is a seismic sector on the planet with accident event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::SEISMIC_ACTIVITY,
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

    public function testRopePreventsAccidentEvent(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::SEISMIC_ACTIVITY], $I),
            explorators: new PlayerCollection([$this->chun]),
        );

        // given there is a seismic sector on the planet with accident event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::SEISMIC_ACTIVITY,
            events: [PlanetSectorEvent::ACCIDENT_3_5 => 1]
        );

        // given Chun has a rope
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::ROPE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        $chunHealthBeforeEvent = $this->chun->getHealthPoint();

        // when accident event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then Chun health is the same as before the event, because she has a rope
        $I->assertEquals(
            expected: $chunHealthBeforeEvent,
            actual: $this->chun->getHealthPoint(),
        );
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

    public function testFuelEventWithDrillDoublesLootAmount(FunctionalTester $I): void
    {
        // given there is only fuel event in fuel sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::HYDROCARBON,
            events: [PlanetSectorEvent::FUEL_6 => 1]
        );

        // given Chun has a drill
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::DRILL,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::HYDROCARBON], $I),
            explorators: new PlayerCollection([$this->chun])
        );

        // when fuel event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then daedalus has an fuel status with 12 fuel
        /** @var ChargeStatus $daedalusFuelStatus */
        $daedalusFuelStatus = $this->daedalus->getStatusByName(DaedalusStatusEnum::EXPLORATION_FUEL);
        $I->assertEquals(12, $daedalusFuelStatus->getCharge());
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
            ->map(static fn (GameEquipment $gameEquipment) => $gameEquipment->getLogName())
            ->toArray();
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
        $I->assertTrue(\in_array($artefact, $planetPlaceEquipments, true));
    }

    public function testKillRandomEventKillsOneExplorator(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::SEISMIC_ACTIVITY], $I),
            explorators: $this->players
        );

        // given only kill random event can happen in seismic sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::SEISMIC_ACTIVITY,
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

    public function testFightEventIsPreventedByWhiteFlag(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: $this->players
        );

        // given only fight and nothing to report events can happen in intelligent sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            events: ['fight_1' => PHP_INT_MAX - 1, 'nothing_to_report' => 1]
        );

        // given Chun has a white flag
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::WHITE_FLAG,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // when I try to dispatch fight event
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then the fight event is not dispatched
        $I->dontSeeInRepository(
            entity: ExplorationLog::class,
            params: [
                'eventName' => PlanetSectorEvent::FIGHT,
            ]
        );
    }

    public function testFightEventIsPreventedByDiplomat(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: $this->players
        );

        // given only fight and nothing to report events can happen in intelligent sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            events: ['fight_1' => PHP_INT_MAX - 1, 'nothing_to_report' => 1]
        );

        // given Chun is a diplomat
        $this->addSkillToPlayer(SkillEnum::DIPLOMAT, $I, $this->chun);

        // when I try to dispatch fight event
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then the fight event is not dispatched
        $I->dontSeeInRepository(
            entity: ExplorationLog::class,
            params: [
                'eventName' => PlanetSectorEvent::FIGHT,
            ]
        );
    }

    public function testFightEventIsPreventedByPolyvalent(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: $this->players
        );

        // given only fight and nothing to report events can happen in intelligent sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            events: ['fight_1' => PHP_INT_MAX - 1, 'nothing_to_report' => 1]
        );

        // given Chun is a polyvalent
        $this->givenPlayerHasSkill($this->chun, SkillEnum::POLYVALENT, $I);

        // when I try to dispatch fight event
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then the fight event is not dispatched
        $I->dontSeeInRepository(
            entity: ExplorationLog::class,
            params: [
                'eventName' => PlanetSectorEvent::FIGHT,
            ]
        );
    }

    public function testFightImprovesStephenTriumph(FunctionalTester $I): void
    {
        $stephen = $this->givenStephenWithSpacesuit($I);

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: $this->players
        );

        // given only fight event can happen in intelligent sector
        $this->givenOnlyThisEventCanHappenInSector(
            event: PlanetSectorEvent::FIGHT_8,
            sector: PlanetSectorEnum::INTELLIGENT,
        );

        $this->givenEveryoneHasZeroTriumph();

        // when fight is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then Stephen gets 2 personal triumph for fight event
        $I->assertEquals(2, $stephen->getTriumph());
        $I->assertEquals(0, $this->chun->getTriumph());
    }

    public function testFightDoesNotImproveStephenTriumphWhenNotActiveExplorator(FunctionalTester $I): void
    {
        $stephen = $this->givenStephenWithSpacesuit($I);

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: new PlayerCollection([$this->chun, $this->kuanTi, $this->janice, $this->derek])
        );

        // given only fight event can happen in intelligent sector
        $this->givenOnlyThisEventCanHappenInSector(
            event: PlanetSectorEvent::FIGHT_8,
            sector: PlanetSectorEnum::INTELLIGENT,
        );

        $this->givenEveryoneHasZeroTriumph();

        // when fight is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then Stephen gets no triumph
        $I->assertEquals(0, $stephen->getTriumph());
    }

    public function testFightImprovesCustomAlienDownTriumphWhenNoDamageDealt(FunctionalTester $I): void
    {
        $this->givenCustomAlienDownConfigRewardsWithTriumph(7);

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: new PlayerCollection([$this->chun, $this->kuanTi, $this->janice, $this->derek])
        );

        // given chun has 2 grenades (+6 strength)
        $this->gameEquipmentService->createGameEquipmentsFromName(
            equipmentName: ItemEnum::GRENADE,
            equipmentHolder: $this->chun,
            quantity: 2,
        );

        // given only fight event can happen in intelligent sector of strength 8
        $this->givenOnlyThisEventCanHappenInSector(
            event: PlanetSectorEvent::FIGHT_8,
            sector: PlanetSectorEnum::INTELLIGENT,
        );

        $this->givenEveryoneHasZeroTriumph();

        // when fight is dispatched (team strength: 8)
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then every active explorer (Chun and Kuan Ti) gets 7 triumph
        $I->assertEquals(7, $this->chun->getTriumph());
        $I->assertEquals(7, $this->kuanTi->getTriumph());
        $I->assertEquals(0, $this->janice->getTriumph());
        $I->assertEquals(0, $this->derek->getTriumph());
    }

    public function testFightDoesNotImproveCustomAlienDownTriumphWhenDamageDealt(FunctionalTester $I): void
    {
        $this->givenCustomAlienDownConfigRewardsWithTriumph(7);

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: new PlayerCollection([$this->chun, $this->kuanTi, $this->janice, $this->derek])
        );

        // given only fight event can happen in intelligent sector of strength 8
        $this->givenOnlyThisEventCanHappenInSector(
            event: PlanetSectorEvent::FIGHT_8,
            sector: PlanetSectorEnum::INTELLIGENT,
        );

        $this->givenEveryoneHasZeroTriumph();

        // when fight is dispatched (team strength: 2)
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then no one gets triumph
        $I->assertEquals(0, $this->chun->getTriumph());
        $I->assertEquals(0, $this->kuanTi->getTriumph());
        $I->assertEquals(0, $this->janice->getTriumph());
        $I->assertEquals(0, $this->derek->getTriumph());
    }

    public function testInsectsImproveJaniceTriumph(FunctionalTester $I): void
    {
        // given Janice not lost
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::LOST,
            holder: $this->janice,
            tags: [],
            time: new \DateTime(),
        );

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INSECT], $I),
            explorators: $this->players
        );

        $this->givenEveryoneHasZeroTriumph();

        // when fight is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then Janice gets 3 personal triumph for encountering a life form
        $I->assertEquals(3, $this->janice->getTriumph());
        $I->assertEquals(0, $this->chun->getTriumph());
    }

    public function testInsectsFromPreventedFightImproveJaniceTriumphOnce(FunctionalTester $I): void
    {
        // given Janice not lost
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::LOST,
            holder: $this->janice,
            tags: [],
            time: new \DateTime(),
        );

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INSECT], $I),
            explorators: $this->players
        );

        // given sector only has fight event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::INSECT,
            events: [
                PlanetSectorEvent::FIGHT_15 => 1,
            ]
        );

        $this->givenEveryoneHasZeroTriumph();

        // when fight is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then Janice gets 3 personal triumph for encountering a life form
        $I->assertEquals(3, $this->janice->getTriumph());
    }

    public function fightPreventedShouldNotGrantJaniceTriumph(FunctionalTester $I): void
    {
        // given Janice not lost
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::LOST,
            holder: $this->janice,
            tags: [],
            time: new \DateTime(),
        );

        // given Janice is diplomat
        $this->addSkillToPlayer(SkillEnum::DIPLOMAT, $I, $this->janice);

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::RUINS], $I),
            explorators: $this->players
        );

        // given sector has fight and nothing to report event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::RUINS,
            events: [
                PlanetSectorEvent::NOTHING_TO_REPORT => 1,
                PlanetSectorEvent::FIGHT_15 => PHP_INT_MAX - 1,
            ]
        );

        $this->givenEveryoneHasZeroTriumph();

        // when fight is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then Janice gets no personal triumph
        $I->assertEquals(0, $this->janice->getTriumph());
    }

    public function testInsectsNotImproveJaniceTriumphWhenLost(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INSECT], $I),
            explorators: $this->players
        );

        $this->givenEveryoneHasZeroTriumph();

        // when fight is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then Janice gets no personal triumph
        $I->assertEquals(0, $this->janice->getTriumph());
    }

    public function testProvisionEvent(FunctionalTester $I): void
    {
        // given Janice not lost
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::LOST,
            holder: $this->janice,
            tags: [],
            time: new \DateTime(),
        );

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::OCEAN], $I),
            explorators: $this->players
        );

        // given only provision event can happen in oceanic sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::OCEAN,
            events: [PlanetSectorEvent::PROVISION_3 => 1]
        );

        $this->givenEveryoneHasZeroTriumph();

        // when provision event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then I should see 3 alien steaks in planet place
        $I->assertCount(3, $this->daedalus->getPlanetPlace()->getEquipments()->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === GameRationEnum::ALIEN_STEAK));

        // then I should see 3 public logs in planet place to tell an explorator has found an alien steak
        $roomLogs = $I->grabEntitiesFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlanetPlace()->getLogName(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::FOUND_ITEM_IN_EXPLORATION,
            ]
        );
        $I->assertCount(3, $roomLogs);
        $roomLogParameters = $roomLogs[0]->getParameters();
        $I->assertEquals(GameRationEnum::ALIEN_STEAK, $roomLogParameters['target_item']);

        // then the founder should be Chun, Kuan-Ti or Janice (not Derek - stuck in ship)
        $I->assertTrue(\in_array($roomLogParameters['character'], [$this->chun->getLogName(), $this->kuanTi->getLogName(), $this->janice->getLogName()], true));

        // then Janice should get 3 triumph
        $I->assertEquals(3, $this->janice->getTriumph());
        $I->assertEquals(0, $this->chun->getTriumph());
    }

    public function testHarvestEvent(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::FRUIT_TREES], $I),
            explorators: $this->players
        );

        // given only harvest event can happen in fruit trees sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::FRUIT_TREES,
            events: [PlanetSectorEvent::HARVEST_3 => 1]
        );

        // when harvest event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then I should see 3 alien fruits in planet place
        $I->assertCount(
            expectedCount: 3,
            haystack: $this->daedalus->getPlanetPlace()->getEquipments()->filter(static fn (GameEquipment $gameEquipment) => GameFruitEnum::getAlienFruits()->contains($gameEquipment->getName()))
        );

        // then I should see 3 public logs in planet place to tell an explorator has found an alien fruit
        $roomLogs = $I->grabEntitiesFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlanetPlace()->getLogName(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::FOUND_ITEM_IN_EXPLORATION,
            ]
        );
        $I->assertCount(3, $roomLogs);
        $roomLogParameters = $roomLogs[0]->getParameters();
        $I->assertTrue(GameFruitEnum::getAlienFruits()->contains($roomLogParameters['target_item']));

        // then the founder should be Chun or Kuan-Ti (not Janice or Derek - lost or stuck in ship)
        $I->assertTrue(\in_array($roomLogParameters['character'], [$this->chun->getLogName(), $this->kuanTi->getLogName()], true));
    }

    public function testHarvestEventShouldGiveMoreFruitsWithAPolyvalent(FunctionalTester $I): void
    {
        // given Chun is a polyvalent
        $this->addSkillToPlayer(SkillEnum::POLYVALENT, $I);

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::FRUIT_TREES], $I),
            explorators: $this->players
        );

        // given only harvest event can happen in fruit trees sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::FRUIT_TREES,
            events: [PlanetSectorEvent::HARVEST_3 => 1]
        );

        // when harvest event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then I should see 4 alien fruits in planet place
        $I->assertCount(
            expectedCount: 4,
            haystack: $this->daedalus->getPlanetPlace()->getEquipments()->filter(static fn (GameEquipment $gameEquipment) => GameFruitEnum::getAlienFruits()->contains($gameEquipment->getName()))
        );
    }

    public function testDiseaseEventCreatesADiseaseForOneExplorator(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::FOREST], $I),
            explorators: $this->players
        );

        // given only disease event can happen in forest sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::FOREST,
            events: [PlanetSectorEvent::DISEASE => 1]
        );

        // when disease event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then one of the explorators has a disease
        if ($this->player->getMedicalConditions()->isEmpty()) {
            $diseasedPlayer = $this->player2;

            /** @var PlayerDisease $caughtDisease */
            $caughtDisease = $this->player2->getMedicalConditions()->getByDiseaseType(MedicalConditionTypeEnum::DISEASE)->first() ?: null;
            $I->assertNotNull($caughtDisease);
        } else {
            $diseasedPlayer = $this->player;

            /** @var PlayerDisease $caughtDisease */
            $caughtDisease = $this->player->getMedicalConditions()->getByDiseaseType(MedicalConditionTypeEnum::DISEASE)->first() ?: null;
            $I->assertNotNull($caughtDisease);
        }

        // then I should see a private room log for diseased player telling they caught a disease
        $diseaseLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlanetPlace()->getLogName(),
                'playerInfo' => $diseasedPlayer->getPlayerInfo(),
                'visibility' => VisibilityEnum::PRIVATE,
                'log' => LogEnum::DISEASE_BY_ALIEN_TRAVEL,
            ]
        );

        // then the disease log should be properly parameterized
        $I->assertEquals($caughtDisease->getDiseaseConfig()->getDiseaseName(), $diseaseLog->getParameters()['disease']);
    }

    public function testDiseaseEventDoesNotCreateTheSameDiseasePlayerAlreadyHas(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::FOREST], $I),
            explorators: new ArrayCollection([$this->player])
        );

        // given only disease event can happen in forest sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::FOREST,
            events: [PlanetSectorEvent::DISEASE => 1]
        );

        // given player1 has a migraine
        $disease = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::MIGRAINE,
            player: $this->player,
            reasons: [],
        );

        // given only migraine disease can happen in forest sector
        /** @var DiseaseCauseConfig $explorationDiseaseCauseConfig */
        $explorationDiseaseCauseConfig = $this->daedalus
            ->getGameConfig()
            ->getDiseaseCauseConfig()
            ->filter(
                static fn (DiseaseCauseConfig $diseaseCauseConfig) => $diseaseCauseConfig->getCauseName() === DiseaseCauseEnum::EXPLORATION
            )
            ->first();
        $explorationDiseaseCauseConfig->setDiseases([DiseaseEnum::MIGRAINE => 1]);

        // when disease event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then player1 still has the same disease
        $I->assertEquals($disease, $this->player->getMedicalConditions()->getByDiseaseType(MedicalConditionTypeEnum::DISEASE)->first());

        // then I still see a private room log for the player telling they caught a disease
        $diseaseLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlanetPlace()->getLogName(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'visibility' => VisibilityEnum::PRIVATE,
                'log' => LogEnum::DISEASE_BY_ALIEN_TRAVEL,
            ]
        );

        // then the disease log should be properly parameterized
        $I->assertArrayHasKey('disease', $diseaseLog->getParameters());
    }

    public function testDiseaseEventDoesNotCreateDiseaseForMushPlayer(FunctionalTester $I): void
    {
        // given KT is a mush
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player2,
            tags: [],
            time: new \DateTime(),
        );

        // given an exploration is created with KT only
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::FOREST], $I),
            explorators: new ArrayCollection([$this->player2])
        );

        // given only disease event can happen in forest sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::FOREST,
            events: [PlanetSectorEvent::DISEASE => 1]
        );

        // when disease event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then the player is not diseased
        $I->assertEmpty($this->player->getMedicalConditions());

        // then I still see a private room log for the player telling they caught a disease
        $diseaseLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlanetPlace()->getLogName(),
                'playerInfo' => $this->player2->getPlayerInfo(),
                'visibility' => VisibilityEnum::PRIVATE,
                'log' => LogEnum::DISEASE_BY_ALIEN_TRAVEL,
            ]
        );

        // then the disease log should be properly parameterized
        $I->assertEquals('true', $diseaseLog->getParameters()['is_player_mush']);
        $I->assertArrayHasKey('disease', $diseaseLog->getParameters());
    }

    public function testStarmapEvent(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::CRISTAL_FIELD], $I),
            explorators: $this->players
        );

        // given only starmap event can happen in cristal field sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::CRISTAL_FIELD,
            events: [PlanetSectorEvent::STARMAP => 1]
        );

        // given Raluca stays in the ship
        $raluca = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::RALUCA);
        $this->players->add($raluca);

        // given everyone has 0 triumph
        foreach ($this->players as $player) {
            $player->setTriumph(0);
        }

        // when starmap event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then I should see a starmap fragment in planet place
        $I->assertTrue($this->daedalus->getPlanetPlace()->hasEquipmentByName(ItemEnum::STARMAP_FRAGMENT));

        // then everyone should gain 6 triumph
        foreach ($this->players as $player) {
            $I->assertEquals(6, $player->getTriumph());
        }

        // then I should see 1 public logs in planet place to tell an explorator has found a starmap fragment
        $roomLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlanetPlace()->getLogName(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::FOUND_ITEM_IN_EXPLORATION,
            ]
        );
        $roomLogParameters = $roomLog->getParameters();

        // then the founder should be Chun or Kuan-Ti (not Janice or Derek - lost or stuck in ship)
        $I->assertTrue(\in_array($roomLogParameters['character'], [$this->chun->getLogName(), $this->kuanTi->getLogName()], true));
    }

    public function testSecondStarmapEvent(FunctionalTester $I): void
    {
        // given starmap already created
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::STARMAP_FRAGMENT,
            equipmentHolder: $this->derek,
            reasons: [],
            time: new \DateTime()
        );

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: $this->players
        );

        // given only artefact event can happen in intelligent life sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            events: [PlanetSectorEvent::ARTEFACT => 1]
        );

        // given only starmap can be obtained from artefact event
        /** @var PlanetSectorEventConfig $sectorEventConfig */
        $sectorEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => PlanetSectorEvent::ARTEFACT]);
        $sectorEventConfig->setOutputTable(['starmap_fragment' => 1]);

        // given everyone has 0 triumph
        foreach ($this->players as $player) {
            $player->setTriumph(0);
        }

        // when starmap event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then I should see a starmap fragment in planet place
        $I->assertTrue($this->daedalus->getPlanetPlace()->hasEquipmentByName(ItemEnum::STARMAP_FRAGMENT));

        // then Chun and Kuan-Ti should gain 1 triumph (not Janice or Derek - lost or stuck in ship)
        $I->assertEquals(1, $this->chun->getTriumph());
        $I->assertEquals(1, $this->kuanTi->getTriumph());
        $I->assertEquals(0, $this->janice->getTriumph());
        $I->assertEquals(0, $this->derek->getTriumph());

        // then I should see 1 public logs in planet place to tell an explorator has found a starmap fragment
        $roomLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlanetPlace()->getLogName(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::FOUND_ITEM_IN_EXPLORATION,
            ]
        );
        $roomLogParameters = $roomLog->getParameters();

        // then the founder should be Chun or Kuan-Ti (not Janice or Derek - lost or stuck in ship)
        $I->assertTrue(\in_array($roomLogParameters['character'], [$this->chun->getLogName(), $this->kuanTi->getLogName()], true));
    }

    public function testAgainEvent(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::DESERT], $I),
            explorators: $this->players
        );
        $desertPlanetSector = $exploration->getPlanet()->getSectors()->filter(static fn ($sector) => $sector->getName() === PlanetSectorEnum::DESERT)->first();

        // given only again event can happen in desert sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::DESERT,
            events: [PlanetSectorEvent::AGAIN => 1]
        );

        // when again event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then desert planet sector should be unvisited
        $I->assertFalse($desertPlanetSector->isVisited());
    }

    public function testItemLostEvent(FunctionalTester $I): void
    {
        // given Chun has an iTrackie
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::ITRACKIE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: $this->players
        );

        // given only item lost event can happen in intelligent sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            events: [PlanetSectorEvent::ITEM_LOST => 1]
        );

        // when item lost event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then Chun does not have the iTrackie anymore
        $I->assertFalse($this->chun->hasEquipmentByName(ItemEnum::ITRACKIE));

        // then I should see 1 public log in planet place to tell an explorator has lost an item
        $roomLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlanetPlace()->getLogName(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::LOST_ITEM_IN_EXPLORATION,
            ]
        );

        $I->assertEquals($this->chun->getLogName(), $roomLog->getParameters()['character']);
        $I->assertEquals(ItemEnum::ITRACKIE, $roomLog->getParameters()['target_item']);

        // then the exploration log should be properly parameterized
        /** @var ExplorationLog $explorationLog */
        $explorationLog = $exploration->getClosedExploration()->getLogs()->last();
        $I->assertEquals($this->chun->getLogName(), $explorationLog->getParameters()['character']);
        $I->assertEquals(ItemEnum::ITRACKIE, $explorationLog->getParameters()['item']);
    }

    public function testItemLostEventDoesNotDestroySpacesuit(FunctionalTester $I): void
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
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: $this->players
        );

        // given only item lost event can happen in intelligent sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            events: [PlanetSectorEvent::ITEM_LOST => 1]
        );

        // when item lost event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then Chun still has the spacesuit
        $I->assertTrue($this->chun->hasEquipmentByName(GearItemEnum::SPACESUIT));

        // then I should not see any public log in planet place to tell an explorator has lost an item
        $I->dontSeeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlanetPlace()->getLogName(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::LOST_ITEM_IN_EXPLORATION,
            ]
        );

        // then the exploration log should be nothing to report one
        /** @var ExplorationLog $explorationLog */
        $explorationLog = $exploration->getClosedExploration()->getLogs()->last();
        $I->assertEquals(PlanetSectorEvent::NOTHING_TO_REPORT, $explorationLog->getEventName());
    }

    public function testItemLostEventWithNoItemsToDestroy(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: $this->players
        );

        // given only item lost event can happen in intelligent sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            events: [PlanetSectorEvent::ITEM_LOST => 1]
        );

        // when item lost event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then I should not see any public log in planet place to tell an explorator has lost an item
        $I->dontSeeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlanetPlace()->getLogName(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::LOST_ITEM_IN_EXPLORATION,
            ]
        );

        // then the exploration log should be nothing to report one
        /** @var ExplorationLog $explorationLog */
        $explorationLog = $exploration->getClosedExploration()->getLogs()->last();
        $I->assertEquals(PlanetSectorEvent::NOTHING_TO_REPORT, $explorationLog->getEventName());
    }

    public function testBackEvent(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::VOLCANIC_ACTIVITY], $I),
            explorators: $this->players
        );
        $closedExploration = $exploration->getClosedExploration();

        // given only back event can happen in volcanoes sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::VOLCANIC_ACTIVITY,
            events: [PlanetSectorEvent::BACK => 1]
        );

        // when back event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then the exploration should be finished
        $I->assertTrue($closedExploration->isExplorationFinished());

        // then notifications should be sent to non-lost explorers
        $I->assertTrue($this->chun->hasNotificationByMessage(PlayerNotificationEnum::EXPLORATION_CLOSED_RETURN_EVENT_VOLCANIC_ACTIVITY->toString()));
        $I->assertTrue($this->kuanTi->hasNotificationByMessage(PlayerNotificationEnum::EXPLORATION_CLOSED_RETURN_EVENT_VOLCANIC_ACTIVITY->toString()));
        $I->assertTrue($this->derek->hasNotificationByMessage(PlayerNotificationEnum::EXPLORATION_CLOSED_RETURN_EVENT_VOLCANIC_ACTIVITY->toString()));
        $I->assertFalse($this->janice->hasNotificationByMessage(PlayerNotificationEnum::EXPLORATION_CLOSED_RETURN_EVENT_VOLCANIC_ACTIVITY->toString()));
    }

    public function testPlayerLostEvent(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::COLD], $I),
            explorators: $this->players
        );

        // given only player lost event can happen in cold sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::COLD,
            events: [PlanetSectorEvent::PLAYER_LOST => 1]
        );

        // when player lost event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then Chun or Kuan-Ti is lost and be in planet depths
        if (!$this->chun->hasStatus(PlayerStatusEnum::LOST)) {
            $I->assertTrue($this->kuanTi->hasStatus(PlayerStatusEnum::LOST));
            $I->assertEquals(
                expected: RoomEnum::PLANET_DEPTHS,
                actual: $this->kuanTi->getPlace()->getName(),
            );
            $lostPlayer = $this->kuanTi;
        } else {
            $I->assertTrue($this->chun->hasStatus(PlayerStatusEnum::LOST));
            $I->assertEquals(
                expected: RoomEnum::PLANET_DEPTHS,
                actual: $this->chun->getPlace()->getName(),
            );
            $lostPlayer = $this->chun;
        }

        // then I should see a private log in planet place to tell an explorator is lost
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlanetPlace()->getLogName(),
                'playerInfo' => $lostPlayer->getPlayerInfo(),
                'visibility' => VisibilityEnum::PRIVATE,
                'log' => StatusEventLogEnum::LOST_IN_EXPLORATION,
            ]
        );

        // then exploration log should be properly parameterized
        $explorationLog = $exploration->getClosedExploration()->getLogs()->last();
        $I->assertEquals($lostPlayer->getLogName(), $explorationLog->getParameters()['character']);

        // then I should see a lost sector on the planet
        /** @var ?PlanetSector $lostSector */
        $lostSector = $exploration->getPlanet()->getSectors()->filter(static fn ($sector) => $sector->getName() === PlanetSectorEnum::LOST)->first() ?: null;
        $I->assertNotNull($lostSector);

        // then this sector should be revealed
        $I->assertTrue($lostSector->isRevealed());

        // then I should see a lost crewmate alert
        $I->seeInRepository(
            entity: Alert::class,
            params: ['name' => AlertEnum::LOST_CREWMATE],
        );
    }

    public function testPlayerLostEventDoesNotTargetPlayerWithACompass(FunctionalTester $I): void
    {
        // given Chun has a compass
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::QUADRIMETRIC_COMPASS,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given an exploration is created with Chun
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::COLD], $I),
            explorators: new ArrayCollection([$this->chun])
        );

        // given only player lost event can happen in cold sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::COLD,
            events: [PlanetSectorEvent::PLAYER_LOST => 1]
        );

        // when player lost event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then Chun is not lost
        $I->assertFalse($this->chun->hasStatus(PlayerStatusEnum::LOST));

        // then the event should be nothing to report
        $explorationLog = $exploration->getClosedExploration()->getLogs()->last();
        $I->assertEquals(PlanetSectorEvent::NOTHING_TO_REPORT, $explorationLog->getEventName());
    }

    public function testFindLostEvent(FunctionalTester $I): void
    {
        // given Janice has 10 morale points
        $this->janice->setMoralPoint(10);

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::LOST], $I),
            explorators: $this->players
        );

        // given Janice is in Planet Depths
        $this->janice->changePlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::PLANET_DEPTHS));

        // given only find lost event can happen in lost sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::LOST,
            events: [PlanetSectorEvent::FIND_LOST => 1]
        );

        // when find lost event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then Janice is not lost anymore
        $I->assertFalse($this->janice->hasStatus(PlayerStatusEnum::LOST));

        // then Janice should be in Planet place
        $I->assertEquals(
            expected: RoomEnum::PLANET,
            actual: $this->janice->getPlace()->getName(),
        );

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

        // then I should not see a lost crewmate alert
        $I->dontSeeInRepository(
            entity: Alert::class,
            params: ['name' => AlertEnum::LOST_CREWMATE],
        );
    }

    public function testKillLostEvent(FunctionalTester $I): void
    {
        // given an exploration is created without Janice
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::LOST], $I),
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

        // then I should not see a lost crewmate alert
        $I->dontSeeInRepository(
            entity: Alert::class,
            params: ['name' => AlertEnum::LOST_CREWMATE],
        );
    }

    public function accidentShouldDealOneLessDamageToASurvivalist(FunctionalTester $I): void
    {
        $this->givenChunIsASurvivalist($I);

        $exploration = $this->givenAnExplorationIsCreatedOnSectorForPlayers(
            sectorName: PlanetSectorEnum::SEISMIC_ACTIVITY,
            players: [$this->chun],
            I: $I
        );

        $this->givenOnlyThisEventCanHappenInSector(
            event: PlanetSectorEvent::ACCIDENT_3_5,
            sector: PlanetSectorEnum::SEISMIC_ACTIVITY,
        );

        $this->givenAccidentDealsOneDamage($I);

        $this->givenChunHasTenHealthPoints();

        $this->whenExplorationEventIsDispatched($exploration);

        $this->thenChunShouldHaveHealthPoints(10, $I);
    }

    public function tiredShouldDealOneLessDamageToASurvivalist(FunctionalTester $I): void
    {
        $this->givenChunIsASurvivalist($I);

        $exploration = $this->givenAnExplorationIsCreatedOnSectorForPlayers(
            sectorName: PlanetSectorEnum::DESERT,
            players: [$this->chun],
            I: $I
        );

        $this->givenOnlyThisEventCanHappenInSector(
            event: PlanetSectorEvent::TIRED_2,
            sector: PlanetSectorEnum::DESERT,
        );

        $this->givenChunHasTenHealthPoints();

        $this->whenExplorationEventIsDispatched($exploration);

        $this->thenChunShouldHaveHealthPoints(9, $I);
    }

    public function fightShouldDealOneLessDamageToASurvivalist(FunctionalTester $I): void
    {
        $this->givenChunIsASurvivalist($I);

        $exploration = $this->givenAnExplorationIsCreatedOnSectorForPlayers(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            players: [$this->chun],
            I: $I
        );

        $this->givenOnlyThisEventCanHappenInSector(
            event: 'fight_1',
            sector: PlanetSectorEnum::INTELLIGENT,
        );

        $this->givenChunHasTenHealthPoints();

        $this->whenExplorationEventIsDispatched($exploration);

        $this->thenChunShouldHaveHealthPoints(10, $I);
    }

    public function provisionShouldGiveOneExtraAlienSteakToASurvivalist(FunctionalTester $I): void
    {
        $this->givenChunIsASurvivalist($I);

        $exploration = $this->givenAnExplorationIsCreatedOnSectorForPlayers(
            sectorName: PlanetSectorEnum::RUMINANT,
            players: [$this->chun],
            I: $I
        );

        $this->givenOnlyThisEventCanHappenInSector(
            event: PlanetSectorEvent::PROVISION_4,
            sector: PlanetSectorEnum::RUMINANT,
        );

        $this->whenExplorationEventIsDispatched($exploration);

        $this->thenIShouldSeeFiveAlienSteaksOnPlanet($I);
    }

    public function killRandomShouldNotKillSurvivalistPlayerIfThereIsSomeoneElseToKill(FunctionalTester $I): void
    {
        $this->givenChunIsASurvivalist($I);

        $exploration = $this->givenAnExplorationIsCreatedOnSectorForPlayers(
            sectorName: PlanetSectorEnum::SEISMIC_ACTIVITY,
            players: [$this->chun, $this->kuanTi],
            I: $I
        );

        $this->givenOnlyThisEventCanHappenInSector(
            event: PlanetSectorEvent::KILL_RANDOM,
            sector: PlanetSectorEnum::SEISMIC_ACTIVITY,
        );

        $this->whenExplorationEventIsDispatched($exploration);

        $this->thenPlayerShouldBeAlive($this->chun, $I);
        $this->thenPlayerShouldBeDead($this->kuanTi, $I);
    }

    public function killRandomShouldKillSurvivalistPlayerIfThereIsNoOneElseToKill(FunctionalTester $I): void
    {
        $this->givenChunIsASurvivalist($I);

        $this->givenOnlyThisEventCanHappenInSector(
            event: PlanetSectorEvent::KILL_RANDOM,
            sector: PlanetSectorEnum::SEISMIC_ACTIVITY,
        );

        $exploration = $this->givenAnExplorationIsCreatedOnSectorForPlayers(
            sectorName: PlanetSectorEnum::SEISMIC_ACTIVITY,
            players: [$this->chun],
            I: $I
        );

        $this->whenExplorationEventIsDispatched($exploration);

        $this->thenPlayerShouldBeDead($this->chun, $I);
    }

    public function traitorExploratorShouldHaveAPrivateLogWhenTriggeringNegativeEvent(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::TRAITOR, $I);

        $this->givenOnlyThisEventCanHappenInSector(
            event: PlanetSectorEvent::NOTHING_TO_REPORT,
            sector: PlanetSectorEnum::LANDING,
        );

        $this->givenOnlyThisEventCanHappenInSector(
            event: PlanetSectorEvent::FIGHT_12,
            sector: PlanetSectorEnum::INTELLIGENT,
        );

        $exploration = $this->givenAnExplorationIsCreatedOnSectorForPlayers(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            players: [$this->chun, $this->kuanTi],
            I: $I
        );

        $this->whenExplorationEventIsDispatched($exploration);

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: ':mush: Votre action de traîtrise a favorisé un incident fâcheux.',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::TRAITOR_WORKED,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I
        );

        $I->cantSeeInRepository(
            entity: RoomLog::class,
            params: [
                'visibility' => VisibilityEnum::PRIVATE,
                'log' => LogEnum::TRAITOR_WORKED,
                'playerInfo' => $this->kuanTi->getPlayerInfo(),
            ]
        );

        $I->assertEquals(1, $this->chun->getPlayerInfo()->getStatistics()->getTraitorUsed());
        $I->assertEquals(0, $this->kuanTi->getPlayerInfo()->getStatistics()->getTraitorUsed());
    }

    public function traitorExploratorShouldNotHaveAPrivateLogWhenTriggeringPositiveEvent(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::TRAITOR, $I);

        $this->givenOnlyThisEventCanHappenInSector(
            event: PlanetSectorEvent::NOTHING_TO_REPORT,
            sector: PlanetSectorEnum::LANDING,
        );

        $this->givenOnlyThisEventCanHappenInSector(
            event: PlanetSectorEvent::ARTEFACT,
            sector: PlanetSectorEnum::INTELLIGENT,
        );

        $exploration = $this->givenAnExplorationIsCreatedOnSectorForPlayers(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            players: [$this->chun, $this->kuanTi],
            I: $I
        );

        $this->whenExplorationEventIsDispatched($exploration);

        $I->cantSeeInRepository(
            entity: RoomLog::class,
            params: [
                'visibility' => VisibilityEnum::PRIVATE,
                'log' => LogEnum::TRAITOR_WORKED,
            ]
        );

        $I->assertEquals(0, $this->chun->getPlayerInfo()->getStatistics()->getTraitorUsed());
        $I->assertEquals(0, $this->kuanTi->getPlayerInfo()->getStatistics()->getTraitorUsed());
    }

    private function givenChunIsASurvivalist(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::SURVIVALIST, $I, $this->chun);
    }

    private function givenAnExplorationIsCreatedOnSectorForPlayers(string $sectorName, array $players, FunctionalTester $I): Exploration
    {
        return $this->createExploration(
            planet: $this->createPlanet([$sectorName], $I),
            explorators: new ArrayCollection($players)
        );
    }

    private function givenOnlyThisEventCanHappenInSector(string $event, string $sector): void
    {
        $this->setupPlanetSectorEvents(
            sectorName: $sector,
            events: [$event => 1]
        );
    }

    private function givenAccidentDealsOneDamage(FunctionalTester $I): void
    {
        $accidentEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => PlanetSectorEvent::ACCIDENT_3_5]);
        $accidentEventConfig->setOutputTable([1 => 1]);
    }

    private function givenChunHasTenHealthPoints(): void
    {
        $this->chun->setHealthPoint(10);
    }

    private function givenStephenWithSpacesuit(FunctionalTester $I): Player
    {
        $stephen = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::STEPHEN);
        $this->players->add($stephen);

        // given Stephen has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $stephen,
            reasons: [],
            time: new \DateTime(),
        );

        return $stephen;
    }

    private function givenEveryoneHasZeroTriumph(): void
    {
        foreach ($this->players as $player) {
            $player->setTriumph(0);
        }
    }

    private function givenLostPlayer(Player $player): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenCustomAlienDownConfigRewardsWithTriumph(int $quantity): void
    {
        $this->daedalus->getGameConfig()->getTriumphConfig()->getByNameOrThrow(TriumphEnum::CM_ALIEN_DOWN)->setQuantity($quantity);
    }

    private function whenExplorationEventIsDispatched(Exploration $exploration): void
    {
        $this->explorationService->dispatchExplorationEvent($exploration);
    }

    private function thenChunShouldHaveHealthPoints(int $healthPoints, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $healthPoints,
            actual: $this->chun->getHealthPoint(),
        );
    }

    private function thenIShouldSeeFiveAlienSteaksOnPlanet(FunctionalTester $I): void
    {
        $I->assertCount(5, $this->daedalus->getPlanetPlace()->getAllEquipmentsByName(GameRationEnum::ALIEN_STEAK));
    }

    private function thenIShouldSeeFourAlienFruitsOnPlanet(FunctionalTester $I): void
    {
        $I->assertCount(4, $this->daedalus->getPlanetPlace()->getEquipments()->filter(static fn (GameEquipment $gameEquipment) => GameFruitEnum::getAlienFruits()->contains($gameEquipment->getName())));
    }

    private function thenPlayerShouldBeAlive(Player $player, FunctionalTester $I): void
    {
        $I->assertTrue($player->isAlive());
    }

    private function thenPlayerShouldBeDead(Player $player, FunctionalTester $I): void
    {
        $I->assertFalse($player->isAlive());
    }

    private function givenPlayerHasSkill(Player $player, SkillEnum $skill, FunctionalTester $I): void
    {
        $this->addSkillToPlayer($skill, $I, $player);
    }
}
