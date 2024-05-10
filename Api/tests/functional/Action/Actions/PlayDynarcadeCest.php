<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\PlayDynarcade;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class PlayDynarcadeCest
{
    private PlayDynarcade $playDynarcadeAction;
    private ActionConfig $action;

    public function _before(FunctionalTester $I)
    {
        $this->playDynarcadeAction = $I->grabService(PlayDynarcade::class);
        $this->action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::PLAY_ARCADE]);
    }

    public function testActionIsVisible(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $alphaBay2 */
        $alphaBay2 = $I->have(Place::class, [
            'name' => RoomEnum::ALPHA_BAY_2,
            'daedalus' => $daedalus,
        ]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::CHAO]);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::DYNARCADE]);

        /** @var Player $gamerPlayer */
        $gamerPlayer = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $alphaBay2,
        ]);
        $gamerPlayer->setPlayerVariables($characterConfig);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($gamerPlayer, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $gamerPlayer->setPlayerInfo($playerInfo);
        $I->refreshEntities($gamerPlayer);

        $dynarcade = new GameEquipment($alphaBay2);
        $dynarcade
            ->setName(EquipmentEnum::DYNARCADE)
            ->setEquipment($equipmentConfig);
        $I->haveInRepository($dynarcade);

        $this->playDynarcadeAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $dynarcade,
            player: $gamerPlayer,
            target: $dynarcade);

        $I->assertTrue($this->playDynarcadeAction->isVisible());
        $I->assertNull($this->playDynarcadeAction->cannotExecuteReason());
    }

    public function testCannotExecuteActionIfEquipmentBroken(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $alphaBay2 */
        $alphaBay2 = $I->have(Place::class, [
            'name' => RoomEnum::ALPHA_BAY_2,
            'daedalus' => $daedalus,
        ]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::CHAO]);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::DYNARCADE]);

        /** @var Player $gamerPlayer */
        $gamerPlayer = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $alphaBay2,
        ]);
        $gamerPlayer->setPlayerVariables($characterConfig);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($gamerPlayer, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $gamerPlayer->setPlayerInfo($playerInfo);
        $I->refreshEntities($gamerPlayer);

        $dynarcade = new GameEquipment($alphaBay2);
        $dynarcade
            ->setName(EquipmentEnum::DYNARCADE)
            ->setEquipment($equipmentConfig);
        $I->haveInRepository($dynarcade);

        $brokenStatusConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => EquipmentStatusEnum::BROKEN]);
        $status = new Status($dynarcade, $brokenStatusConfig);

        $I->haveInRepository($status);
        $I->refreshEntities($dynarcade);

        $this->playDynarcadeAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $dynarcade,
            player: $gamerPlayer,
            target: $dynarcade
        );

        $I->assertEquals(ActionImpossibleCauseEnum::BROKEN_EQUIPMENT, $this->playDynarcadeAction->cannotExecuteReason());
    }

    public function testSuccessAction(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $alphaBay2 */
        $alphaBay2 = $I->have(Place::class, [
            'name' => RoomEnum::ALPHA_BAY_2,
            'daedalus' => $daedalus,
        ]);

        $successVariable = $this->action->getVariableByName(ActionVariableEnum::PERCENTAGE_SUCCESS);
        $this->action->setSuccessRate(101);
        $I->refreshEntities($this->action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::CHAO]);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::DYNARCADE]);

        /** @var Player $gamerPlayer */
        $gamerPlayer = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $alphaBay2,
        ]);
        $gamerPlayer->setPlayerVariables($characterConfig);
        $gamerPlayer
            ->setActionPoint(3)
            ->setHealthPoint(6)
            ->setMoralPoint(7);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($gamerPlayer, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $gamerPlayer->setPlayerInfo($playerInfo);
        $I->refreshEntities($gamerPlayer);

        $dynarcade = new GameEquipment($alphaBay2);
        $dynarcade->setName(EquipmentEnum::DYNARCADE)
            ->setEquipment($equipmentConfig);
        $I->haveInRepository($dynarcade);

        $this->playDynarcadeAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $dynarcade,
            player: $gamerPlayer,
            target: $dynarcade
        );

        $I->assertEquals(100, $this->playDynarcadeAction->getSuccessRate());
        $result = $this->playDynarcadeAction->execute();

        $I->assertEquals(2, $gamerPlayer->getActionPoint());
        $I->assertEquals(6, $gamerPlayer->getHealthPoint());
        $I->assertEquals(9, $gamerPlayer->getMoralPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $alphaBay2->getName(),
            'playerInfo' => $gamerPlayer->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::PLAY_ARCADE_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testFailAction(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $alphaBay2 */
        $alphaBay2 = $I->have(Place::class, [
            'name' => RoomEnum::ALPHA_BAY_2,
            'daedalus' => $daedalus,
        ]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::CHAO]);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::DYNARCADE]);

        /** @var Player $gamerPlayer */
        $gamerPlayer = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $alphaBay2,
        ]);
        $gamerPlayer->setPlayerVariables($characterConfig);
        $gamerPlayer
            ->setActionPoint(3)
            ->setHealthPoint(6)
            ->setMoralPoint(7);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($gamerPlayer, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $gamerPlayer->setPlayerInfo($playerInfo);
        $I->refreshEntities($gamerPlayer);

        $dynarcade = new GameEquipment($alphaBay2);
        $dynarcade
            ->setName(EquipmentEnum::DYNARCADE)
            ->setEquipment($equipmentConfig);
        $I->haveInRepository($dynarcade);

        $this->action->setSuccessRate(0);

        $this->playDynarcadeAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $dynarcade,
            player: $gamerPlayer,
            target: $dynarcade
        );
        $this->playDynarcadeAction->execute();

        $I->assertEquals(2, $gamerPlayer->getActionPoint());
        $I->assertEquals(5, $gamerPlayer->getHealthPoint());
        $I->assertEquals(7, $gamerPlayer->getMoralPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $alphaBay2->getName(),
            'playerInfo' => $gamerPlayer->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::PLAY_ARCADE_FAIL,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }
}
