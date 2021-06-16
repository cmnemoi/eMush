<?php

namespace Mush\Tests\Alert\Listener;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Listener\RoomSubscriber;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Place\Event\RoomEvent;

class RoomSubscriberCest
{
    private RoomSubscriber $roomSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->roomSubscriber = $I->grabService(RoomSubscriber::class);
    }

    public function testStartFire(FunctionalTester $I)
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

        $roomEvent = new RoomEvent($room, new DateTime());

        $this->roomSubscriber->onStartingFire($roomEvent);

        $I->seeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::FIRES]);
        $I->seeInRepository(AlertElement::class, ['place' => $room]);
    }

    public function testStopFire(FunctionalTester $I)
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

        $roomEvent = new RoomEvent($room, new DateTime());

        $reportedAlert = new AlertElement();
        $reportedAlert->setPlace($room);
        $I->haveInRepository($reportedAlert);

        $alertFire = new Alert();
        $alertFire
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::FIRES)
            ->addAlertElement($reportedAlert)
        ;

        $I->haveInRepository($alertFire);

        $this->roomSubscriber->onStopFire($roomEvent);

        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::FIRES]);
        $I->dontSeeInRepository(AlertElement::class, ['place' => $room]);
    }
}
