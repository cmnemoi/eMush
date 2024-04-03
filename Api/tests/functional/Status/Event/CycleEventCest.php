<?php

namespace Mush\Tests\functional\Status\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusCycleEvent;
use Mush\Status\Listener\StatusCycleSubscriber;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

final class CycleEventCest extends AbstractFunctionalTest
{
    private StatusCycleSubscriber $cycleSubscriber;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->cycleSubscriber = $I->grabService(StatusCycleSubscriber::class);
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
            ->setStartCharge(0)
        ;
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

    public function testFireStatusCycleSubscriber(FunctionalTester $I): void
    {
        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(StatusEnum::FIRE)
            ->setModifierConfigs(new ArrayCollection([]))
            ->buildName(GameConfigEnum::TEST)
            ->setStartCharge(0)
        ;
        $I->haveInRepository($statusConfig);

        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class, [
            'propagatingFireRate' => 100,
            'hullFireDamageRate' => 100,
            'maximumAllowedSpreadingFires' => 2,
        ]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'difficultyConfig' => $difficultyConfig,
            'statusConfigs' => new ArrayCollection([$statusConfig]),
        ]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class, ['name' => GameConfigEnum::TEST]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $daedalus->setDaedalusVariables($daedalusConfig);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, [
            'isFireBreakable' => false,
            'isFireDestroyable' => false,
            'name' => 'door_test',
        ]);

        $doorConfig
            ->setIsFireBreakable(false)
            ->setIsFireDestroyable(false)
        ;

        $door = new Door($room);
        $door
             ->setName('door name')
             ->setEquipment($doorConfig)
        ;

        $room->addDoor($door);
        $room2->addDoor($door);

        $healthPointBefore = $player->getHealthPoint();
        $moralPointBefore = $player->getMoralPoint();
        $hullPointBefore = $daedalus->getHull();

        $time = new \DateTime();

        /** @var ChargeStatus $status */
        $status = $this->statusService->createStatusFromConfig(
            $statusConfig,
            $room,
            [],
            new \DateTime()
        );
        $status->setCharge(1);

        $cycleEvent = new StatusCycleEvent($status, $room, [EventEnum::NEW_CYCLE], $time);

        $I->refreshEntities($player, $daedalus);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertEquals($healthPointBefore - 2, $player->getHealthPoint());
        $I->assertEquals($moralPointBefore, $player->getMoralPoint());
        $I->assertEquals($hullPointBefore - 2, $daedalus->getHull());

        $I->assertEquals(StatusEnum::FIRE, $room2->getStatuses()->first()->getName());
        $I->assertEquals(0, $room2->getStatuses()->first()->getCharge());
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
}
