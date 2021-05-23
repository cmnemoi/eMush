<?php

namespace Mush\Tests\Situation\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Situation\Entity\Situation;
use Mush\Situation\Enum\SituationEnum;
use Mush\Situation\Listener\EquipmentSubscriber;

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

        $I->seeInRepository(Situation::class, ['daedalus' => $daedalus, 'name' => SituationEnum::NO_GRAVITY, 'isVisible' => true]);
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

        $situation = new Situation($daedalus, SituationEnum::NO_GRAVITY, true);
        $I->haveInRepository($situation);

        $this->equipmentSubscriber->onEquipmentFixed($equipmentEvent);

        $I->dontSeeInRepository(Situation::class, ['daedalus' => $daedalus, 'name' => SituationEnum::NO_GRAVITY, 'isVisible' => true]);
    }
}
