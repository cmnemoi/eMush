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
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;

class BoringSpeechActionCest
{
    private BoringSpeech $BoringSpeechAction;

    public function _before(FunctionalTester $I)
    {
        $this->BoringSpeechAction = $I->grabService(BoringSpeech::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
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

        /** @var CharacterConfig $characterConfig */
        $speakerConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);

        $listenerConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $speaker = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'movementPoint' => 6,
            'characterConfig' => $speakerConfig,
        ]);

        /** @var Player $targetPlayer */
        $listener = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'movementPoint' => 6,
            'characterConfig' => $listenerConfig,
        ]);

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
            'player' => $speaker->getId(),
            'log' => ActionLogEnum::BORING_SPEECH,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->assertEquals($this->BoringSpeechAction->cannotExecuteReason(), ActionImpossibleCauseEnum::ALREADY_DID_BORING_SPEECH);
    }
}
