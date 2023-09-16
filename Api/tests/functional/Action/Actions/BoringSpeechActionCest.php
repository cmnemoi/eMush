<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\BoringSpeech;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\FunctionalTester;
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
        $didBoringSpeechStatus = new ChargeStatusConfig();
        $didBoringSpeechStatus = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['statusName' => PlayerStatusEnum::DID_BORING_SPEECH]);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'roomName']);

        $action = $I->grabEntityFromRepository(Action::class, ['actionName' => ActionEnum::BORING_SPEECH]);

        /** @var CharacterConfig $speakerConfig */
        $speakerConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::CHUN]);

        /** @var CharacterConfig $listenerConfig */
        $listenerConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::DEREK]);

        /** @var Player $speaker */
        $speaker = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $speaker->setPlayerVariables($speakerConfig);
        $speaker->setActionPoint(10)->setMovementPoint(6);
        $I->flushToDatabase($speaker);

        /** @var User $user */
        $user = $I->have(User::class);
        $speakerInfo = new PlayerInfo($speaker, $user, $speakerConfig);
        $I->haveInRepository($speakerInfo);
        $speaker->setPlayerInfo($speakerInfo);
        $I->refreshEntities($speaker);

        /** @var Player $listener */
        $listener = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $listener->setPlayerVariables($listenerConfig);
        $listener->setActionPoint(10)->setMovementPoint(6);
        $I->flushToDatabase($listener);
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
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $speaker->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::BORING_SPEECH,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->assertEquals($this->BoringSpeechAction->cannotExecuteReason(), ActionImpossibleCauseEnum::ALREADY_DID_BORING_SPEECH);
    }
}
