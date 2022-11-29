<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\BoringSpeech;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;

class BoringSpeechActionCest
{
    private BoringSpeech $BoringSpeechAction;

    public function _before(FunctionalTester $I)
    {
        $this->BoringSpeechAction = $I->grabService(BoringSpeech::class);
    }

    public function testBoringSpeech(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'roomName']);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(2)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::BORING_SPEECH)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost($actionCost);
        $I->haveInRepository($action);

        $didBoringSpeechStatus = new ChargeStatusConfig();
        $didBoringSpeechStatus
            ->setName(PlayerStatusEnum::DID_BORING_SPEECH)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
            ->setStartCharge(1)
            ->setAutoRemove(true)
            ->setGameConfig($gameConfig)
        ;

        $I->haveInRepository($didBoringSpeechStatus);

        /** @var CharacterConfig $speakerConfig */
        $speakerConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var CharacterConfig $listenerConfig */
        $listenerConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $speaker */
        $speaker = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'movementPoint' => 6,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $speakerInfo = new PlayerInfo($speaker, $user, $speakerConfig);
        $I->haveInRepository($speakerInfo);
        $speaker->setPlayerInfo($speakerInfo);
        $I->refreshEntities($speaker);

        /** @var Player $listener */
        $listener = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'movementPoint' => 6,
        ]);
        $listenerInfo = new PlayerInfo($listener, $user, $listenerConfig);
        $I->haveInRepository($listenerInfo);
        $listener->setPlayerInfo($listenerInfo);
        $I->refreshEntities($listener);

        $this->BoringSpeechAction->loadParameters($action, $speaker);

        $I->assertTrue($this->BoringSpeechAction->isVisible());
        $I->assertNull($this->BoringSpeechAction->cannotExecuteReason());

        $this->BoringSpeechAction->execute();

        $I->assertEquals(8, $speaker->getActionPoint());
        $I->assertEquals(6, $speaker->getMovementPoint());

        $I->assertEquals(10, $listener->getActionPoint());
        $I->assertEquals(9, $listener->getMovementPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'playerInfo' => $speaker->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::BORING_SPEECH,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->assertEquals($this->BoringSpeechAction->cannotExecuteReason(), ActionImpossibleCauseEnum::ALREADY_DID_BORING_SPEECH);
    }
}
