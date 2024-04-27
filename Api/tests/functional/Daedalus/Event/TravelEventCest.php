<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Daedalus\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionEnum;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Enum\AlertEnum;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetName;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Entity\HunterTarget;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Event\HunterCycleEvent;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TravelEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private ExplorationServiceInterface $explorationService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->explorationService = $I->grabService(ExplorationServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testHunterAlertIsDeletedIfNoHunterAttackingOnTravelLaunched(FunctionalTester $I): void
    {
        // given some (simple) hunters are spawn
        $hunterPoolEvent = new HunterPoolEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime()
        );
        $this->eventService->callEvent($hunterPoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        // when travel is launched
        $daedalusEvent = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_LAUNCHED);

        // then no hunter is attacking, therefore no hunter alert is present
        $I->assertEmpty($this->daedalus->getAttackingHunters());
        $I->dontSeeInRepository(Alert::class, [
            'name' => AlertEnum::HUNTER,
            'daedalus' => $this->daedalus,
        ]);
    }

    public function testTravelFinishedEventCreatesANeronAnnouncement(FunctionalTester $I): void
    {
        // when travel is launched and finished
        $this->launchAndFinishesTravel();

        // then a neron announcement is created
        $I->seeInRepository(Message::class, [
            'neron' => $this->daedalus->getDaedalusInfo()->getNeron(),
            'message' => NeronMessageEnum::TRAVEL_ARRIVAL,
        ]);
    }

    public function testTravelWhenExploringFinishesExplorationAndKillsExplorators(FunctionalTester $I): void
    {
        // given an exploration is ongoing
        $this->createExploration($I);

        // when travel is launched
        $daedalusEvent = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [ActionEnum::LEAVE_ORBIT],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_LAUNCHED);

        // then exploration is deleted
        $I->assertNull($this->daedalus->getExploration());

        // then explorator is dead
        $I->assertCount(1, $this->daedalus->getPlayers()->getPlayerDead());

        // then the death log exists on the planet (but is hidden)
        /** @var RoomLog $deathLog */
        $deathLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlanetPlace()->getName(),
                'log' => LogEnum::DEATH,
                'visibility' => VisibilityEnum::HIDDEN,
            ]
        );

        // then the death log character name is the explorator name
        $I->assertEquals($this->player->getLogName(), $deathLog->getParameters()['target_character']);

        // then the death log end cause is abandoned
        $I->assertEquals(EndCauseEnum::ABANDONED, $deathLog->getParameters()['end_cause']);
    }

    public function testTravelWhenExploringDoesNotAddLootedOxygenToDaedalus(FunctionalTester $I): void
    {
        // given daedalus has 10 oxygen
        $this->daedalus->setOxygen(10);

        // given an exploration is ongoing
        $this->createExploration($I);

        // given there is an oxygen sector with an oxygen event
        $oxygenSector = $this->planet->getSectors()->filter(static fn (PlanetSector $sector) => $sector->getName() === PlanetSectorEnum::OXYGEN)->first();

        /** @var PlanetSectorEventConfig $oxygenEventConfig */
        $oxygenEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => PlanetSectorEvent::OXYGEN . '_8']);

        // when oxygen event is dispatched
        $oxygenEvent = new PlanetSectorEvent(
            planetSector: $oxygenSector,
            config: $oxygenEventConfig,
        );
        $this->eventService->callEvent($oxygenEvent, $oxygenEventConfig->getEventName());

        // when travel is launched
        $daedalusEvent = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [ActionEnum::LEAVE_ORBIT],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_LAUNCHED);

        // no oxygen is added
        $I->assertEquals(10, $this->daedalus->getOxygen());

        // exploration oxygen status does not exist anymore
        $daedalusOxygenStatus = $this->daedalus->getStatusByName(DaedalusStatusEnum::EXPLORATION_OXYGEN);
        $I->assertNull($daedalusOxygenStatus);
    }

    public function testHunterAfterATravelDoesNotShootRightAway(FunctionalTester $I): void
    {
        // given a hunter is spawn
        $hunter = $this->createHunterFromName($I, $this->daedalus, HunterEnum::HUNTER);

        // given this hunter is aiming at the daedalus
        $hunter->setTarget(new HunterTarget($hunter));

        // given it has a 100% chance to hit
        $hunter->setHitChance(100);

        // given it does 1 damage per hit
        $hunter->getHunterConfig()->setDamageRange([1 => 1]);

        $daedalusHullBeforeTravel = $this->daedalus->getHull();

        // given I launch a travel
        $this->launchAndFinishesTravel();

        // when a cycle passes
        $hunterEvent = new HunterCycleEvent($this->daedalus, [], new \DateTime());
        $this->eventService->callEvent($hunterEvent, HunterCycleEvent::HUNTER_NEW_CYCLE);

        // then hunters should not have shot, so daedalus should not have lost hull
        $I->assertEquals(
            expected: $daedalusHullBeforeTravel,
            actual: $this->daedalus->getHull()
        );

        // when a cycle passes again
        $hunterEvent = new HunterCycleEvent($this->daedalus, [], new \DateTime());
        $this->eventService->callEvent($hunterEvent, HunterCycleEvent::HUNTER_NEW_CYCLE);

        // then hunters should have shot, so daedalus should have lost hull
        $I->assertLessThan(
            expected: $daedalusHullBeforeTravel,
            actual: $this->daedalus->getHull()
        );
    }

    public function testTraxAfterATravelShootRightAway(FunctionalTester $I): void
    {
        // given a trax is spawn
        $trax = $this->createHunterFromName($I, $this->daedalus, HunterEnum::TRAX);

        // given this trax is aiming at the daedalus
        $trax->setTarget(new HunterTarget($trax));

        // given it has a 100% chance to hit
        $trax->setHitChance(100);

        // given trax and hunter are the only hunters which can spawn
        $this->daedalus->getGameConfig()->setHunterConfigs(
            $this->daedalus
                ->getGameConfig()
                ->getHunterConfigs()
                ->filter(
                    static fn (
                        HunterConfig $hunterConfig
                    ) => $hunterConfig->getHunterName() === HunterEnum::TRAX
                        || $hunterConfig->getHunterName() === HunterEnum::HUNTER
                )
        );

        // given hunter has no chance to spawn
        $hunterConfig = $this->daedalus->getGameConfig()->getHunterConfigs()->getHunter(HunterEnum::HUNTER);
        $hunterConfig->setSpawnDifficulty(20000);

        // given daedalus is day 5 so trax can spawn
        $this->daedalus->setDay(5);

        $daedalusHullBeforeTravel = $this->daedalus->getHull();

        // given I launch a travel
        $daedalusEvent = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_LAUNCHED);

        // given travel finishes
        $daedalusEvent = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_FINISHED);

        // when a cycle passes
        $hunterEvent = new HunterCycleEvent($this->daedalus, [], new \DateTime());
        $this->eventService->callEvent($hunterEvent, HunterCycleEvent::HUNTER_NEW_CYCLE);

        // then hunters should have shot, so daedalus should have lost hull
        $I->assertLessThan(
            expected: $daedalusHullBeforeTravel,
            actual: $this->daedalus->getHull()
        );
    }

    public function testTravelFinishedSpawnsHalfOfTheHuntersOfPreviousWave(FunctionalTester $I): void
    {
        // given 10 hunters are spawn
        $this->daedalus->setHunterPoints(100);
        $hunterPoolEvent = new HunterPoolEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime()
        );
        $this->eventService->callEvent($hunterPoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        // given 4 trax are spawn
        for ($i = 0; $i < 4; ++$i) {
            $this->createHunterFromName($I, $this->daedalus, HunterEnum::TRAX);
        }

        // when travel is launched and finished
        $this->launchAndFinishesTravel();

        // then 5 hunters are spawn
        $I->assertCount(5, $this->daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::HUNTER));

        // then 4 trax are still there
        $I->assertCount(4, $this->daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::TRAX));

        // when another travel is launched and finished
        $this->launchAndFinishesTravel();

        // then 3 hunters are spawn
        $I->assertCount(3, $this->daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::HUNTER));

        // then 4 trax are still there
        $I->assertCount(4, $this->daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::TRAX));
    }

    public function testTravelFinishedSpawnsAHalfPowerWaveIfThereWereNoHunterToFleeFrom(FunctionalTester $I): void
    {
        // given daedalus has enough points to spawn 11 hunters
        $this->daedalus->setHunterPoints(110);

        // when travel is launched and finished
        $this->launchAndFinishesTravel();

        // then 6 hunters are spawn
        $I->assertCount(6, $this->daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::HUNTER));
    }

    public function testTravelFinishedSpawnsOneHunterIfThereWasNoHunterToFleeFromAndNoEnoughPower(FunctionalTester $I): void
    {
        // given there are no attacking hunters
        $I->assertEquals(0, $this->daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::HUNTER)->count());

        // given daedalus has no points to spawn hunters
        $this->daedalus->setHunterPoints(0);

        // when travel is launched and finished
        $this->launchAndFinishesTravel();

        // then 1 hunter is spawn
        $I->assertCount(1, $this->daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::HUNTER));
    }

    public function testTravelFinishedSpawnsMinus75PercentOfPreviousWaveWithTrailReducer(FunctionalTester $I): void
    {
        // given there are 10 hunters
        for ($i = 0; $i < 10; ++$i) {
            $this->createHunterFromName($I, $this->daedalus, HunterEnum::HUNTER);
        }

        // given the trail reducer project is finished
        $trailReducer = $this->daedalus->getProjectByName(ProjectName::TRAIL_REDUCER);
        $this->finishProject($trailReducer, $this->chun, $I);

        // then daedalus should have 1 modifier
        $I->assertCount(1, $this->daedalus->getModifiers());

        // when travel is launched and finished
        $this->launchAndFinishesTravel();

        // then 3 hunters are spawn
        $I->assertCount(3, $this->daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::HUNTER));
    }

    public function testTravelFinishedSpawnsAThreeQuartersPowerWaveWithTrailReducerIfThereWereNoHunterToFleeFrom(FunctionalTester $I): void
    {
        // given daedalus has enough points to spawn 10 hunters
        $this->daedalus->setHunterPoints(100);

        // given the trail reducer project is finished
        $trailReducer = $this->daedalus->getProjectByName(ProjectName::TRAIL_REDUCER);
        $this->finishProject($trailReducer, $this->chun, $I);

        // when travel is launched and finished
        $this->launchAndFinishesTravel();

        // then 3 hunters are spawn
        $I->assertCount(3, $this->daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::HUNTER));
    }

    // TODO: Fix this test
    // public function testTravelFinishedSpawnsOneHunterWithTrailReducerIfThereWasNoHunterToFleeFromAndNoEnoughPower(FunctionalTester $I): void
    // {
    //     // given there are no attacking hunters
    //     $I->assertEquals(0, $this->daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::HUNTER)->count());

    //     // given daedalus has no points to spawn hunters
    //     $this->daedalus->setHunterPoints(0);

    //     // given the trail reducer project is finished
    //     $trailReducer = $this->daedalus->getProjectByName(ProjectName::TRAIL_REDUCER);
    //     $this->finishProject($trailReducer, $this->chun, $I);

    //     // then daedalus should have 1 modifier
    //     $I->assertCount(1, $this->daedalus->getModifiers());

    //     // when travel is launched and finished
    //     $this->launchAndFinishesTravel();

    //     // then 1 hunter is spawn
    //     $I->assertCount(1, $this->daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::HUNTER));
    // }

    public function testHunterAfterMultipleTravelsDoesNotShootRightAway(FunctionalTester $I): void
    {
        // given a hunter is spawn
        $hunter = $this->createHunterFromName($I, $this->daedalus, HunterEnum::HUNTER);

        // given this hunter is aiming at the daedalus
        $hunter->setTarget(new HunterTarget($hunter));

        // given it has a 100% chance to hit
        $hunter->setHitChance(100);

        // given it does 1 damage per hit
        $hunter->getHunterConfig()->setDamageRange([1 => 1]);

        $daedalusHullBeforeTravel = $this->daedalus->getHull();

        // given I launch a travel
        $this->launchAndFinishesTravel();

        // given a cycle passes
        $hunterEvent = new HunterCycleEvent($this->daedalus, [], new \DateTime());
        $this->eventService->callEvent($hunterEvent, HunterCycleEvent::HUNTER_NEW_CYCLE);

        // given I launch another travel
        $this->launchAndFinishesTravel();

        // when another cycle passes
        $hunterEvent = new HunterCycleEvent($this->daedalus, [], new \DateTime());
        $this->eventService->callEvent($hunterEvent, HunterCycleEvent::HUNTER_NEW_CYCLE);

        // then hunters should have not shot, so daedalus should have not lost hull
        $I->assertEquals(
            expected: $daedalusHullBeforeTravel,
            actual: $this->daedalus->getHull()
        );
    }

    private function createExploration(FunctionalTester $I)
    {
        // given there is Icarus Bay on this Daedalus
        $icarusBay = $this->createExtraPlace(RoomEnum::ICARUS_BAY, $I, $this->daedalus);

        // given player is in Icarus Bay
        $this->player->changePlace($icarusBay);

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
            ->setSize(3);
        $I->haveInRepository($this->planet);

        $desertSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::DESERT . '_default']);
        $desertSector = new PlanetSector($desertSectorConfig, $this->planet);
        $I->haveInRepository($desertSector);

        $sismicSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::SISMIC_ACTIVITY . '_default']);
        $sismicSector = new PlanetSector($sismicSectorConfig, $this->planet);
        $I->haveInRepository($sismicSector);

        $oxygenSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::OXYGEN . '_default']);
        $oxygenSector = new PlanetSector($oxygenSectorConfig, $this->planet);
        $I->haveInRepository($oxygenSector);

        $hydroCarbonSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::HYDROCARBON . '_default']);
        $hydroCarbonSector = new PlanetSector($hydroCarbonSectorConfig, $this->planet);
        $I->haveInRepository($hydroCarbonSector);

        $this->planet->setSectors(new ArrayCollection([$desertSector, $sismicSector, $oxygenSector, $hydroCarbonSector]));

        // given the Daedalus is in orbit around the planet
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );

        // given there is an exploration with an explorator
        $this->explorationService->createExploration(
            players: new PlayerCollection([$this->player]),
            explorationShip: $this->icarus,
            numberOfSectorsToVisit: 2,
            reasons: ['test'],
        );
    }

    private function createHunterFromName(FunctionalTester $I, Daedalus $daedalus, string $hunterName): Hunter
    {
        /** @var HunterConfig $hunterConfig */
        $hunterConfig = $daedalus->getGameConfig()->getHunterConfigs()->getHunter($hunterName);
        if (!$hunterConfig) {
            throw new \Exception("Hunter config not found for hunter name {$hunterName}");
        }

        $hunter = new Hunter($hunterConfig, $daedalus);
        $hunter->setHunterVariables($hunterConfig);
        $daedalus->addHunter($hunter);

        $I->haveInRepository($hunter);
        $I->haveInRepository($daedalus);

        return $hunter;
    }

    private function launchAndFinishesTravel(): void
    {
        $daedalusEvent = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [ActionEnum::ADVANCE_DAEDALUS],
            time: new \DateTime()
        );
        $daedalusEvent->setAuthor($this->chun);
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_LAUNCHED);

        $daedalusEvent = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [ActionEnum::ADVANCE_DAEDALUS],
            time: new \DateTime()
        );
        $daedalusEvent->setAuthor($this->chun);
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_FINISHED);
    }
}
