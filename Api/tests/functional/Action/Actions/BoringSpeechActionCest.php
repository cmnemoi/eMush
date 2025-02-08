<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\BoringSpeech;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Enum\ChannelScopeEnum;
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
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

/**
 * @internal
 */
final class BoringSpeechActionCest extends AbstractFunctionalTest
{
    private ActionConfig $boringSpeechActionConfig;
    private BoringSpeech $boringSpeechAction;
    private ChooseSkillUseCase $chooseSkillUseCase;
    private PlayerServiceInterface $playerService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->boringSpeechActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::BORING_SPEECH]);
        $this->boringSpeechAction = $I->grabService(BoringSpeech::class);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
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

        $mushChannel = new Channel();
        $mushChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::MUSH);
        $I->haveInRepository($mushChannel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'roomName']);

        $action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::BORING_SPEECH]);

        /** @var CharacterConfig $speakerConfig */
        $speakerConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::DEREK]);

        /** @var CharacterConfig $listenerConfig */
        $listenerConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::CHUN]);

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
        $I->haveInRepository($speaker);

        $this->chooseSkillUseCase->execute(new ChooseSkillDto(skill: SkillEnum::MOTIVATOR, player: $speaker));

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
        $I->haveInRepository($listener);

        $this->boringSpeechAction->loadParameters($action, $speaker, $speaker);

        $I->assertTrue($this->boringSpeechAction->isVisible());
        $I->assertNull($this->boringSpeechAction->cannotExecuteReason());

        $this->boringSpeechAction->execute();

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

        $I->assertEquals($this->boringSpeechAction->cannotExecuteReason(), ActionImpossibleCauseEnum::ALREADY_DID_BORING_SPEECH);
    }

    public function shouldNotGiveMovementPointToDeadPlayer(FunctionalTester $I): void
    {
        $this->givenChunIsMotivator($I);

        $this->givenKuanTiHasMovementPoints(10);

        $this->givenKuanTiIsDead();

        $this->whenChunGivesBoringSpeech();

        $this->thenKuanTiShouldHaveMovementPoints(10, $I);
    }

    private function givenChunIsMotivator(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::MOTIVATOR, $I);
    }

    private function givenKuanTiHasMovementPoints(int $points): void
    {
        $this->kuanTi->setMovementPoint($points);
    }

    private function givenKuanTiIsDead(): void
    {
        $this->playerService->killPlayer(player: $this->kuanTi, endReason: EndCauseEnum::ABANDONED);
    }

    private function whenChunGivesBoringSpeech(): void
    {
        $this->boringSpeechAction->loadParameters($this->boringSpeechActionConfig, $this->chun, $this->chun);
        $this->boringSpeechAction->execute();
    }

    private function thenKuanTiShouldHaveMovementPoints(int $expectedPoints, FunctionalTester $I): void
    {
        $I->assertEquals($expectedPoints, $this->kuanTi->getMovementPoint());
    }
}
