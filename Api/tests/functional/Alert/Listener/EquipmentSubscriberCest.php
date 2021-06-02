<?php

namespace Mush\Tests\Alert\Listener;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Listener\EquipmentSubscriber;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\RoomLog\Enum\VisibilityEnum;

class EquipmentSubscriberCest
{
    private EquipmentSubscriber $equipmentSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->equipmentSubscriber = $I->grabService(EquipmentSubscriber::class);
    }

    public function testBreakGravitySimulator(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $gravitySimulatorConfig */
        $gravitySimulatorConfig = $I->have(EquipmentConfig::class, ['name' => EquipmentEnum::GRAVITY_SIMULATOR, 'gameConfig' => $gameConfig]);

        $gravitySimulator = new GameEquipment();
        $gravitySimulator
            ->setName(EquipmentEnum::GRAVITY_SIMULATOR)
            ->setEquipment($gravitySimulatorConfig)
            ->setPlace($room)
        ;

        $I->haveInRepository($gravitySimulator);

        $equipmentEvent = new EquipmentEvent($gravitySimulator, VisibilityEnum::HIDDEN, new DateTime());

        $this->equipmentSubscriber->onEquipmentBroken($equipmentEvent);

        $I->seeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::NO_GRAVITY]);
    }

    public function testFixGravitySimulator(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $gravitySimulatorConfig */
        $gravitySimulatorConfig = $I->have(EquipmentConfig::class, ['name' => EquipmentEnum::GRAVITY_SIMULATOR, 'gameConfig' => $gameConfig]);

        $gravitySimulator = new GameEquipment();
        $gravitySimulator
            ->setName(EquipmentEnum::GRAVITY_SIMULATOR)
            ->setEquipment($gravitySimulatorConfig)
            ->setPlace($room)
        ;

        $I->haveInRepository($gravitySimulator);

        $equipmentEvent = new EquipmentEvent($gravitySimulator, VisibilityEnum::HIDDEN, new DateTime());

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::NO_GRAVITY);

        $I->haveInRepository($alert);

        $reportedAlert = new AlertElement();
        $reportedAlert->setEquipment($gravitySimulator);
        $I->haveInRepository($reportedAlert);

        $alertBroken = new Alert();
        $alertBroken
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::EQUIPMENT_BROKEN)
            ->addAlertElement($reportedAlert)
        ;

        $I->haveInRepository($alertBroken);

        $this->equipmentSubscriber->onEquipmentFixed($equipmentEvent);

        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::NO_GRAVITY]);
    }

    public function testBreakEquipment(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $gravitySimulatorConfig */
        $gravitySimulatorConfig = $I->have(EquipmentConfig::class, ['name' => EquipmentEnum::GRAVITY_SIMULATOR, 'gameConfig' => $gameConfig]);

        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setName(EquipmentEnum::BED)
            ->setEquipment($gravitySimulatorConfig)
            ->setPlace($room)
        ;

        $I->haveInRepository($gameEquipment);

        $equipmentEvent = new EquipmentEvent($gameEquipment, VisibilityEnum::HIDDEN, new DateTime());

        $this->equipmentSubscriber->onEquipmentBroken($equipmentEvent);

        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::NO_GRAVITY]);
        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::BROKEN_DOORS]);
        $I->seeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::EQUIPMENT_BROKEN]);
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
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $gravitySimulatorConfig */
        $gravitySimulatorConfig = $I->have(EquipmentConfig::class, ['name' => EquipmentEnum::GRAVITY_SIMULATOR, 'gameConfig' => $gameConfig]);

        $gameEquipment = new Door();
        $gameEquipment
            ->setName(EquipmentEnum::BED)
            ->setEquipment($gravitySimulatorConfig)
            ->setPlace($room)
        ;

        $I->haveInRepository($gameEquipment);

        $equipmentEvent = new EquipmentEvent($gameEquipment, VisibilityEnum::HIDDEN, new DateTime());

        $this->equipmentSubscriber->onEquipmentBroken($equipmentEvent);

        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::NO_GRAVITY]);
        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::EQUIPMENT_BROKEN]);
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
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $gravitySimulatorConfig */
        $gravitySimulatorConfig = $I->have(EquipmentConfig::class, ['name' => EquipmentEnum::GRAVITY_SIMULATOR, 'gameConfig' => $gameConfig]);

        $gravitySimulator = new GameEquipment();
        $gravitySimulator
            ->setName(EquipmentEnum::BED)
            ->setEquipment($gravitySimulatorConfig)
            ->setPlace($room)
        ;

        $I->haveInRepository($gravitySimulator);

        $equipmentEvent = new EquipmentEvent($gravitySimulator, VisibilityEnum::HIDDEN, new DateTime());

        $reportedAlert = new AlertElement();
        $reportedAlert->setEquipment($gravitySimulator);
        $I->haveInRepository($reportedAlert);

        $alertBroken = new Alert();
        $alertBroken
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::EQUIPMENT_BROKEN)
            ->addAlertElement($reportedAlert)
        ;

        $I->haveInRepository($alertBroken);

        $this->equipmentSubscriber->onEquipmentFixed($equipmentEvent);

        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::NO_GRAVITY]);
    }
}
