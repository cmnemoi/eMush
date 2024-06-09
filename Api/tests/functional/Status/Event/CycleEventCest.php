<?php

namespace Mush\Tests\functional\Status\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Project\Enum\ProjectName;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusCycleEvent;
use Mush\Status\Listener\StatusCycleSubscriber;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

/**
 * @internal
 */
final class CycleEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $equipmentService;
    private StatusCycleSubscriber $cycleSubscriber;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->cycleSubscriber = $I->grabService(StatusCycleSubscriber::class);
        $this->equipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    // tests
    public function testChargeStatusCycleSubscriber(FunctionalTester $I)
    {
        // Cycle Increment
        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::FROZEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setMaxCharge(1)
            ->setAutoRemove(true)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->buildName(GameConfigEnum::TEST)
            ->setStartCharge(0);
        $I->haveInRepository($statusConfig);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'statusConfigs' => new ArrayCollection([$statusConfig]),
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $time = new \DateTime();

        /** @var ChargeStatus $status */
        $status = $this->statusService->createStatusFromConfig(
            $statusConfig,
            $player,
            [],
            new \DateTime()
        );

        $id = $status->getId();

        $cycleEvent = new StatusCycleEvent($status, new Player(), [EventEnum::NEW_CYCLE], $time);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->seeInRepository(ChargeStatus::class, ['id' => $id]);
    }

    public function fireShouldRemoveHealthPointToPlayer(FunctionalTester $I): void
    {
        // given a fire in Chun's room
        $fireStatus = $this->statusService->createStatusFromName(
            statusName: StatusEnum::FIRE,
            holder: $this->chun->getPlace(),
            tags: [],
            time: new \DateTime()
        );

        // when a new cycle passes
        $cycleEvent = new StatusCycleEvent(
            $fireStatus,
            $this->chun->getPlace(),
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->cycleSubscriber->onNewCycle($cycleEvent);

        // then Chun should have lost 2 health points
        $I->assertEquals($this->chun->getCharacterConfig()->getInitHealthPoint() - 2, $this->chun->getHealthPoint());
    }

    public function fireShouldRemoveHullPointsToDaedalus(FunctionalTester $I): void
    {
        // given fire has a 100% chance to damage the hull
        $difficultyConfig = $this->daedalus->getGameConfig()->getDifficultyConfig();
        $difficultyConfig->setHullFireDamageRate(100);

        // given fire damage is 2
        $difficultyConfig->setFireHullDamage([2 => 1]);

        // given a fire in Chun's room
        $fireStatus = $this->statusService->createStatusFromName(
            statusName: StatusEnum::FIRE,
            holder: $this->chun->getPlace(),
            tags: [],
            time: new \DateTime()
        );

        // when a new cycle passes
        $cycleEvent = new StatusCycleEvent(
            $fireStatus,
            $this->chun->getPlace(),
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->cycleSubscriber->onNewCycle($cycleEvent);

        // then Daedalus should have lost 2 hull points
        $I->assertEquals(98, $this->daedalus->getHull());
    }

    public function fireShouldPropagateToAdjacentRooms(FunctionalTester $I): void
    {
        // given fire has a 100% chance to propagate
        $difficultyConfig = $this->daedalus->getGameConfig()->getDifficultyConfig();
        $difficultyConfig->setPropagatingFireRate(100);

        // given a fire in Chun's room
        $fireStatus = $this->statusService->createStatusFromName(
            statusName: StatusEnum::FIRE,
            holder: $this->chun->getPlace(),
            tags: [],
            time: new \DateTime()
        );

        // given Chun's room has a door to the Front Corridor
        $frontCorridor = $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);
        Door::createFromRooms($this->chun->getPlace(), $frontCorridor);

        // when a new cycle passes
        $cycleEvent = new StatusCycleEvent(
            $fireStatus,
            $this->chun->getPlace(),
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->cycleSubscriber->onNewCycle($cycleEvent);

        // then the fire should have propagated to the Front Corridor
        $I->assertTrue($frontCorridor->hasStatus(StatusEnum::FIRE));
    }

    public function propagatedFireShouldBeInactive(FunctionalTester $I): void
    {
        // given fire has a 100% chance to propagate
        $difficultyConfig = $this->daedalus->getGameConfig()->getDifficultyConfig();
        $difficultyConfig->setPropagatingFireRate(100);

        // given a fire in Chun's room
        $fireStatus = $this->statusService->createStatusFromName(
            statusName: StatusEnum::FIRE,
            holder: $this->chun->getPlace(),
            tags: [],
            time: new \DateTime()
        );

        // given Chun's room has a door to the Front Corridor
        $frontCorridor = $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);
        Door::createFromRooms($this->chun->getPlace(), $frontCorridor);

        // when a new cycle passes
        $cycleEvent = new StatusCycleEvent(
            $fireStatus,
            $this->chun->getPlace(),
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->cycleSubscriber->onNewCycle($cycleEvent);

        // then propagated fire should be inactive
        $I->assertEquals(0, $frontCorridor->getStatusByName(StatusEnum::FIRE)->getCharge());
    }

    public function testBrokenEquipmentDoNotGetElectricChargesUpdatesAtCycleChange(FunctionalTester $I): void
    {
        // given a patrol ship
        /** @var EquipmentConfig $patrolShipConfig */
        $patrolShipConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN . '_default']);
        $patrolShip = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $patrolShip->setName(EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN);
        $patrolShip->setEquipment($patrolShipConfig);
        $I->haveInRepository($patrolShip);

        // given the patrol ship has an electric charge status with 1 charge
        /** @var ChargeStatusConfig $electricChargesConfig */
        $electricChargesConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => 'electric_charges_patrol_ship_default']);
        $electricCharges = new ChargeStatus($patrolShip, $electricChargesConfig);
        $electricCharges->setCharge(1);
        $I->haveInRepository($electricCharges);

        // given this patrol ship is broken
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $patrolShip,
            tags: [],
            time: new \DateTime()
        );

        // when the cycle event is triggered
        $cycleEvent = new StatusCycleEvent($electricCharges, $patrolShip, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->cycleSubscriber->onNewCycle($cycleEvent);

        // then the patrol ship electric charges should still have 1 charge
        $I->assertEquals(1, $electricCharges->getCharge());
    }

    public function shouldMakeStarvingStatusAppearAfterThreeDays(FunctionalTester $I): void
    {
        $this->daedalus->setDay(1)->setCycle(1);

        // when 24 cycles pass
        for ($i = 0; $i < 24; ++$i) {
            $cycleEvent = new PlayerCycleEvent($this->chun, [EventEnum::NEW_CYCLE], new \DateTime());
            $this->eventService->callEvent($cycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);
        }

        // then Chun should have Starving warning status
        $I->assertTrue($this->chun->hasStatus(PlayerStatusEnum::STARVING_WARNING));
    }

    public function shouldMakePlayerStarvingAfterThreeDaysAndOneCycle(FunctionalTester $I): void
    {
        // when 25 cycles pass
        for ($i = 0; $i < 25; ++$i) {
            $cycleEvent = new PlayerCycleEvent($this->chun, [EventEnum::NEW_CYCLE], new \DateTime());
            $this->eventService->callEvent($cycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);
        }

        // then Chun should have Starving status
        $I->assertTrue($this->chun->hasStatus(PlayerStatusEnum::STARVING));

        // then Chun should have lost 1 health point
        $I->assertEquals($this->chun->getCharacterConfig()->getInitHealthPoint() - 1, $this->chun->getHealthPoint());
    }

    public function shouldKillFiresIfAutoWateringProjectIsActivated(FunctionalTester $I): void
    {
        // given auto watering project is finished
        $autoWatering = $this->daedalus->getProjectByName(ProjectName::AUTO_WATERING);
        $this->finishProject(
            project: $autoWatering,
            author: $this->chun,
            I: $I
        );

        // given it has a 100% activation rate
        $autoWateringConfig = $autoWatering->getConfig();
        $reflection = new \ReflectionClass($autoWateringConfig);
        $reflection->getProperty('activationRate')->setValue($autoWateringConfig, 100);

        // given Chun's room is on fire
        $fireStatus = $this->statusService->createStatusFromName(
            statusName: StatusEnum::FIRE,
            holder: $this->chun->getPlace(),
            tags: [],
            time: new \DateTime()
        );

        // when a new cycle passes
        $cycleEvent = new StatusCycleEvent(
            $fireStatus,
            $this->chun->getPlace(),
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->cycleSubscriber->onNewCycle($cycleEvent);

        // then the fire should be killed
        $I->assertFalse($this->chun->getPlace()->hasStatus(StatusEnum::FIRE));
    }

    public function shouldIncreaseNumberOfChargesEarnedByTurretWithTurretExtraFireRateProject(FunctionalTester $I): void
    {
        [$turret, $turretCharges] = $this->givenATurretWithOneCharge();

        $this->givenTurretExtraFireRateProjectIsFinished($I);

        $this->whenACyclePassesForTurretCharges($turretCharges);

        $this->thenTurretShouldHaveThreeCharges($turret, $I);
    }

    private function givenATurretWithOneCharge(): array
    {
        $turret = $this->equipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::TURRET_COMMAND,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        $turretCharges = $turret->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES)->setCharge(1);

        return [$turret, $turretCharges];
    }

    private function givenTurretExtraFireRateProjectIsFinished(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::TURRET_EXTRA_FIRE_RATE),
            author: $this->chun,
            I: $I
        );
    }

    private function whenACyclePassesForTurretCharges(ChargeStatus $turretCharges): void
    {
        $cycleEvent = new StatusCycleEvent(
            status: $turretCharges,
            holder: $turretCharges->getOwner(),
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime()
        );
        $this->eventService->callEvent($cycleEvent, StatusCycleEvent::STATUS_NEW_CYCLE);
    }

    private function thenTurretShouldHaveThreeCharges(GameEquipment $turret, FunctionalTester $I): void
    {
        $turretCharges = $turret->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES);
        $I->assertEquals(3, $turretCharges->getCharge());
    }
}
