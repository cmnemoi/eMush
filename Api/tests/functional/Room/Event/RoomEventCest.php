<?php

namespace Mush\Tests\Room\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Room\Entity\Room;
use Mush\Room\Event\RoomEvent;
use Mush\Room\Event\RoomSubscriber;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\StatusEnum;

class RoomEventCest
{
    private RoomSubscriber $roomSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->roomSubscriber = $I->grabService(RoomSubscriber::class);
    }

    // tests
    public function testNewFire(FunctionalTester $I)
    {
        $time = new DateTime();
        /** @var Room $room */
        $room = $I->have(Room::class);

        $roomEvent = new RoomEvent($room, $time);

        $this->roomSubscriber->onStartingFire($roomEvent);

        $I->assertEquals(1, $room->getStatuses()->count());

        /** @var Status $fireStatus */
        $fireStatus = $room->getStatuses()->first();

        $I->assertEquals($room, $fireStatus->getOwner());
        $I->assertEquals(StatusEnum::FIRE, $fireStatus->getName());
    }
}
