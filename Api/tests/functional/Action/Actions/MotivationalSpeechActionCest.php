<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\MotivationalSpeech;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
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
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class MotivationalSpeechActionCest
{
    private MotivationalSpeech $motivationalSpeechAction;
    private ActionConfig $action;

    private ChooseSkillUseCase $chooseSkillUseCase;

    public function _before(FunctionalTester $I)
    {
        $this->motivationalSpeechAction = $I->grabService(MotivationalSpeech::class);
        $this->action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::MOTIVATIONAL_SPEECH]);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
    }

    public function testMotivationalSpeech(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        $mushChannel = new Channel();
        $mushChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::MUSH);
        $I->haveInRepository($mushChannel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'roomName']);

        /** @var CharacterConfig $speakerConfig */
        $speakerConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::JIN_SU]);

        /** @var CharacterConfig $listenerConfig */
        $listenerConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::DEREK]);

        /** @var Player $speaker */
        $speaker = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $speaker->setPlayerVariables($speakerConfig);
        $speaker
            ->setActionPoint(10)
            ->setMoralPoint(6);

        /** @var User $user */
        $user = $I->have(User::class);
        $speakerInfo = new PlayerInfo($speaker, $user, $speakerConfig);
        $I->haveInRepository($speakerInfo);
        $speaker->setPlayerInfo($speakerInfo);
        $I->haveInRepository($speaker);

        $this->chooseSkillUseCase->execute(new ChooseSkillDto(skill: SkillEnum::LEADER, player: $speaker));

        /** @var Player $listener */
        $listener = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'characterConfig' => $listenerConfig,
        ]);
        $listener->setPlayerVariables($listenerConfig);
        $listener
            ->setActionPoint(10)
            ->setMoralPoint(6);
        $listenerInfo = new PlayerInfo($listener, $user, $listenerConfig);
        $I->haveInRepository($listenerInfo);
        $listener->setPlayerInfo($listenerInfo);
        $I->refreshEntities($listener);

        $this->motivationalSpeechAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $speaker,
            player: $speaker
        );

        $I->assertTrue($this->motivationalSpeechAction->isVisible());
        $I->assertNull($this->motivationalSpeechAction->cannotExecuteReason());

        $this->motivationalSpeechAction->execute();

        $I->assertEquals(8, $speaker->getActionPoint());
        $I->assertEquals(6, $speaker->getMoralPoint());

        $I->assertEquals(10, $listener->getActionPoint());
        $I->assertEquals(8, $listener->getMoralPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $speaker->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::MOTIVATIONAL_SPEECH,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
