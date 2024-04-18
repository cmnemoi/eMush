<?php

namespace Mush\Tests\functional\Alert\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionEnum;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Listener\StatusSubscriber;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEventEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\FunctionalTester;

class StatusSubscriberCest
{
    private StatusSubscriber $statusSubscriber;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        $this->statusSubscriber = $I->grabService(StatusSubscriber::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testBreakGravitySimulator(FunctionalTester $I)
    {
        $statusConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => EquipmentStatusEnum::BROKEN]);
        $noGravityConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => DaedalusStatusEnum::NO_GRAVITY]);
        $noGravityRepairedConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => DaedalusStatusEnum::NO_GRAVITY_REPAIRED]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'statusConfigs' => new ArrayCollection([$statusConfig, $noGravityConfig, $noGravityRepairedConfig]),
        ]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $gravitySimulatorConfig */
        $gravitySimulatorConfig = $I->have(EquipmentConfig::class, ['name' => EquipmentEnum::GRAVITY_SIMULATOR, 'gameConfig' => $gameConfig]);

        $gravitySimulator = new GameEquipment($room);
        $gravitySimulator
            ->setName(EquipmentEnum::GRAVITY_SIMULATOR)
            ->setEquipment($gravitySimulatorConfig);

        $I->haveInRepository($gravitySimulator);

        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::BROKEN,
            $gravitySimulator,
            [ActionEnum::SABOTAGE],
            new \DateTime()
        );

        $I->seeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::NO_GRAVITY]);
        $I->assertCount(1, $daedalus->getStatuses());
        $I->assertFalse($daedalus->hasStatus(DaedalusStatusEnum::NO_GRAVITY_REPAIRED));
        $I->assertTrue($daedalus->hasStatus(DaedalusStatusEnum::NO_GRAVITY));
    }

    public function testFixGravitySimulator(FunctionalTester $I)
    {
        $statusConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => EquipmentStatusEnum::BROKEN]);
        $noGravityConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => DaedalusStatusEnum::NO_GRAVITY]);
        $noGravityRepairedConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => DaedalusStatusEnum::NO_GRAVITY_REPAIRED]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'statusConfigs' => new ArrayCollection([$statusConfig, $noGravityConfig, $noGravityRepairedConfig]),
        ]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $gravitySimulatorConfig */
        $gravitySimulatorConfig = $I->have(EquipmentConfig::class, ['name' => EquipmentEnum::GRAVITY_SIMULATOR, 'gameConfig' => $gameConfig]);

        $gravitySimulator = new GameEquipment($room);
        $gravitySimulator
            ->setName(EquipmentEnum::GRAVITY_SIMULATOR)
            ->setEquipment($gravitySimulatorConfig);
        $I->haveInRepository($gravitySimulator);

        $broken = new Status($gravitySimulator, $statusConfig);
        $I->haveInRepository($broken);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::NO_GRAVITY);

        $I->haveInRepository($alert);

        $reportedAlert = new AlertElement();
        $reportedAlert->setEquipment($gravitySimulator);
        $I->haveInRepository($reportedAlert);

        $alertBroken = new Alert();
        $alertBroken
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::BROKEN_EQUIPMENTS)
            ->addAlertElement($reportedAlert);

        $I->haveInRepository($alertBroken);

        $this->statusService->removeStatus(
            EquipmentStatusEnum::BROKEN,
            $gravitySimulator,
            [ActionEnum::SABOTAGE],
            new \DateTime()
        );

        $I->SeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::GRAVITY_REBOOT]);
        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::NO_GRAVITY]);
        $I->assertCount(1, $daedalus->getStatuses());
        $I->assertTrue($daedalus->hasStatus(DaedalusStatusEnum::NO_GRAVITY_REPAIRED));
        $I->assertFalse($daedalus->hasStatus(DaedalusStatusEnum::NO_GRAVITY));

        // now, remove the no_gravity_repaired status
        $this->statusService->removeStatus(DaedalusStatusEnum::NO_GRAVITY_REPAIRED, $daedalus, [], new \DateTime());
        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::GRAVITY_REBOOT]);
        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::NO_GRAVITY]);
        $I->assertCount(0, $daedalus->getStatuses());
        $I->assertFalse($daedalus->hasStatus(DaedalusStatusEnum::NO_GRAVITY_REPAIRED));
        $I->assertFalse($daedalus->hasStatus(DaedalusStatusEnum::NO_GRAVITY));
    }

    public function testBreakEquipment(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $gravitySimulatorConfig */
        $gravitySimulatorConfig = $I->have(EquipmentConfig::class, ['name' => EquipmentEnum::GRAVITY_SIMULATOR, 'gameConfig' => $gameConfig]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setName(EquipmentEnum::BED)
            ->setEquipment($gravitySimulatorConfig);

        $I->haveInRepository($gameEquipment);

        $brokenConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => EquipmentStatusEnum::BROKEN]);
        $statusEvent = new StatusEvent(
            new Status($gameEquipment, $brokenConfig),
            $gameEquipment,
            [RoomEventEnum::CYCLE_FIRE],
            new \DateTime()
        );
        $this->statusSubscriber->onStatusApplied($statusEvent);

        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::NO_GRAVITY]);
        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::BROKEN_DOORS]);
        $I->seeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::BROKEN_EQUIPMENTS]);
        $I->seeInRepository(AlertElement::class, ['equipment' => $gameEquipment]);
    }

    public function testBreakDoor(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $gravitySimulatorConfig */
        $gravitySimulatorConfig = $I->have(EquipmentConfig::class, ['name' => EquipmentEnum::GRAVITY_SIMULATOR, 'gameConfig' => $gameConfig]);

        $gameEquipment = new Door($room);
        $gameEquipment
            ->setName(EquipmentEnum::BED)
            ->setEquipment($gravitySimulatorConfig);

        $I->haveInRepository($gameEquipment);

        $brokenConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => EquipmentStatusEnum::BROKEN]);
        $statusEvent = new StatusEvent(
            new Status($gameEquipment, $brokenConfig),
            $gameEquipment,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->statusSubscriber->onStatusApplied($statusEvent);

        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::NO_GRAVITY]);
        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::BROKEN_EQUIPMENTS]);
        $I->seeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::BROKEN_DOORS]);
        $I->seeInRepository(AlertElement::class, ['equipment' => $gameEquipment]);
    }

    public function testFixEquipment(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $gravitySimulatorConfig */
        $gravitySimulatorConfig = $I->have(EquipmentConfig::class, ['name' => EquipmentEnum::GRAVITY_SIMULATOR, 'gameConfig' => $gameConfig]);

        $gravitySimulator = new GameEquipment($room);
        $gravitySimulator
            ->setName(EquipmentEnum::BED)
            ->setEquipment($gravitySimulatorConfig);

        $I->haveInRepository($gravitySimulator);

        $reportedAlert = new AlertElement();
        $reportedAlert->setEquipment($gravitySimulator);
        $I->haveInRepository($reportedAlert);

        $alertBroken = new Alert();
        $alertBroken
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::BROKEN_EQUIPMENTS)
            ->addAlertElement($reportedAlert);

        $I->haveInRepository($alertBroken);

        $brokenConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => EquipmentStatusEnum::BROKEN]);
        $statusEvent = new StatusEvent(
            new Status($gravitySimulator, $brokenConfig),
            $gravitySimulator,
            [ActionEnum::REPAIR],
            new \DateTime()
        );
        $this->statusSubscriber->onStatusRemoved($statusEvent);

        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::NO_GRAVITY]);
    }

    public function testBreakItem(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var ItemConfig $drillConfig */
        $drillConfig = $I->have(ItemConfig::class, ['name' => ItemEnum::DRILL, 'gameConfig' => $gameConfig]);

        $gameItem = new GameItem($room);
        $gameItem
            ->setName(ItemEnum::DRILL)
            ->setEquipment($drillConfig);

        $I->haveInRepository($gameItem);

        $brokenConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => EquipmentStatusEnum::BROKEN]);
        $statusEvent = new StatusEvent(
            new Status($gameItem, $brokenConfig),
            $gameItem,
            [RoomEventEnum::CYCLE_FIRE],
            new \DateTime()
        );
        $this->statusSubscriber->onStatusApplied($statusEvent);

        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::NO_GRAVITY]);
        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::BROKEN_DOORS]);
        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::BROKEN_EQUIPMENTS]);
        $I->dontSeeInRepository(AlertElement::class, ['equipment' => $gameItem]);
    }
}
