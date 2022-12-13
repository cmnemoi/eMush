<?php

namespace Mush\Tests\RoomLog\Listener;

use App\Tests\FunctionalTester;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Listener\ActionSubscriber;
use Mush\User\Entity\User;

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

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'healthPoint' => 10]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $action = new Action();
        $action->setActionName(ActionEnum::GET_UP);

        $actionEvent = new ActionEvent($action, $player, null);

        $actionResult = new Fail();
        $actionEvent->setActionResult($actionResult);

        $this->actionSubscriber->onResultAction($actionEvent);

        $I->dontSeeInRepository(RoomLog::class, ['playerInfo' => $playerInfo]);

        $actionResult = new Success();
        $actionEvent->setActionResult($actionResult);

        $this->actionSubscriber->onResultAction($actionEvent);

        $I->seeInRepository(RoomLog::class, ['playerInfo' => $playerInfo, 'log' => ActionLogEnum::GET_UP]);
    }
}
