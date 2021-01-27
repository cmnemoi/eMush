<?php

namespace Mush\Tests\RoomLog\Event;

use App\Tests\FunctionalTester;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Event\ActionSubscriber;

class ActionSubscriberCest
{
    private ActionSubscriber $actionSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->actionSubscriber = $I->grabService(ActionSubscriber::class);
    }

    public function testPostAction(FunctionalTester $I)
    {
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var Room $room */
        $room = $I->have(Room::class, ['daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'room' => $room, 'healthPoint' => 10]);

        $action = new Action();
        $action->setName(ActionEnum::GET_UP);

        $actionEvent = new ActionEvent($action, $player);

        $actionResult = new Fail();
        $actionEvent->setActionResult($actionResult);

        $this->actionSubscriber->onResultAction($actionEvent);

        $I->seeInRepository(RoomLog::class, ['player' => $player, 'log' => 'no_log_yet_' . ActionEnum::GET_UP]);

        $actionResult = new Success();
        $actionEvent->setActionResult($actionResult);

        $this->actionSubscriber->onResultAction($actionEvent);

        $I->seeInRepository(RoomLog::class, ['player' => $player, 'log' => ActionLogEnum::GET_UP]);
    }
}
