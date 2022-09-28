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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;

class MotivationalSpeechActionCest
{
    private MotivationalSpeech $MotivationalSpeechAction;

    public function _before(FunctionalTester $I)
    {
        $this->MotivationalSpeechAction = $I->grabService(MotivationalSpeech::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
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

        /** @var CharacterConfig $characterConfig */
        $speakerConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::JIN_SU,
            'actions' => new ArrayCollection([$action]),
        ]);

        $listenerConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $speaker = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
            'characterConfig' => $speakerConfig,
        ]);

        /** @var Player $targetPlayer */
        $listener = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
            'characterConfig' => $listenerConfig,
        ]);

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
            'player' => $speaker->getId(),
            'log' => ActionLogEnum::MOTIVATIONAL_SPEECH,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
