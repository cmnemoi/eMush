<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\MotivationalSpeech;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
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
use Mush\User\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MotivationalSpeechActionCest
{
    private MotivationalSpeech $MotivationalSpeechAction;

    public function _before(FunctionalTester $I)
    {
        $this->MotivationalSpeechAction = $I->grabService(MotivationalSpeech::class);
        $this->eventDispatcher = $I->grabService(EventDispatcherInterface::class);
    }

    public function testMotivationalSpeech(FunctionalTester $I)
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
            ->setName(ActionEnum::MOTIVATIONAL_SPEECH)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost($actionCost);
        $I->haveInRepository($action);

        /** @var CharacterConfig $speakerConfig */
        $speakerConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::JIN_SU,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var CharacterConfig $listenerConfig */
        $listenerConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $speaker */
        $speaker = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
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
            'moralPoint' => 6,
            'characterConfig' => $listenerConfig,
        ]);
        $listenerInfo = new PlayerInfo($listener, $user, $listenerConfig);
        $I->haveInRepository($listenerInfo);
        $listener->setPlayerInfo($listenerInfo);
        $I->refreshEntities($listener);

        $this->MotivationalSpeechAction->loadParameters($action, $speaker);

        $I->assertTrue($this->MotivationalSpeechAction->isVisible());
        $I->assertNull($this->MotivationalSpeechAction->cannotExecuteReason());

        $this->MotivationalSpeechAction->execute();

        $I->assertEquals(8, $speaker->getActionPoint());
        $I->assertEquals(6, $speaker->getMoralPoint());

        $I->assertEquals(10, $listener->getActionPoint());
        $I->assertEquals(8, $listener->getMoralPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'playerInfo' => $speaker->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::MOTIVATIONAL_SPEECH,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
