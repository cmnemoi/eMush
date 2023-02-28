<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\PlayDynarcade;
use Mush\Action\Entity\Action;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\StatusEnum;
use Mush\User\Entity\User;

use Mush\Action\Enum\{
    ActionEnum,
    ActionScopeEnum
};
use Mush\Daedalus\Entity\{
    Daedalus,
    DaedalusInfo
};
use Mush\Equipment\Entity\{
    Config\EquipmentConfig,
    GameItem
};
use Mush\Game\DataFixtures\{
    GameConfigFixtures,
    LocalizationConfigFixtures
};
use Mush\Game\Entity\{
    GameConfig,
    LocalizationConfig
};
use Mush\Game\Enum\{
    ActionOutputEnum,
    GameConfigEnum,
    LanguageEnum,
    VisibilityEnum
};
use Mush\Player\Entity\{
    Config\CharacterConfig,
    Player,
    PlayerInfo
};

class PlayDynarcadeCest
{
    private PlayDynarcade $playDynarcadeAction;

    public function _before(FunctionalTester $I)
    {
        $this->playDynarcadeAction = $I->grabService(PlayDynarcade::class);
    }

    public function testActionIsVisible(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $aplhaBay2 */
        $aplhaBay2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ALPHA_BAY_2]);

        $action = $this->createAction(33);

        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class);
        $equipmentConfig->setActions(new ArrayCollection([$action]));

        /** @var Player $gamerPlayer */
        $gamerPlayer = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $aplhaBay2,
        ]);
        $gamerPlayer->setPlayerVariables($characterConfig);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($gamerPlayer, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $gamerPlayer->setPlayerInfo($playerInfo);
        $I->refreshEntities($gamerPlayer);

        $dynarcade = new GameItem($aplhaBay2);
        $dynarcade->setName(EquipmentEnum::DYNARCADE)
            ->setEquipment($equipmentConfig);
        $I->haveInRepository($dynarcade);

        $this->playDynarcadeAction->loadParameters($action, $gamerPlayer, $dynarcade);

        $I->assertTrue($this->playDynarcadeAction->isVisible());
        $I->assertNull($this->playDynarcadeAction->cannotExecuteReason());
    }

    public function testSuccessAction(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $aplhaBay2 */
        $aplhaBay2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ALPHA_BAY_2]);

        $action = $this->createAction(100);

        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class);
        $equipmentConfig->setActions(new ArrayCollection([$action]));

        /** @var Player $gamerPlayer */
        $gamerPlayer = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $aplhaBay2,
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

        $dynarcade = new GameItem($aplhaBay2);
        $dynarcade->setName(EquipmentEnum::DYNARCADE)
            ->setEquipment($equipmentConfig);
        $I->haveInRepository($dynarcade);

        $this->playDynarcadeAction->loadParameters($action, $gamerPlayer, $dynarcade);

        $this->playDynarcadeAction->execute();

        $I->assertEquals(2, $gamerPlayer->getActionPoint());
        $I->assertEquals(6, $gamerPlayer->getHealthPoint());
        $I->assertEquals(9, $gamerPlayer->getMoralPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $aplhaBay2->getName(),
            'playerInfo' => $gamerPlayer->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::PLAY_ARCADE_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testFailAction(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);

        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setStatusName(StatusEnum::ATTEMPT)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->buildName(GameConfigEnum::TEST);

        $I->haveInRepository($attemptConfig);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig->setStatusConfigs(new ArrayCollection([$attemptConfig]));
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $aplhaBay2 */
        $aplhaBay2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ALPHA_BAY_2]);

        $action = $this->createAction(0);

        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class);
        $equipmentConfig->setActions(new ArrayCollection([$action]));

        /** @var Player $gamerPlayer */
        $gamerPlayer = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $aplhaBay2,
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

        $dynarcade = new GameItem($aplhaBay2);
        $dynarcade->setName(EquipmentEnum::DYNARCADE)
            ->setEquipment($equipmentConfig);
        $I->haveInRepository($dynarcade);

        $this->playDynarcadeAction->loadParameters($action, $gamerPlayer, $dynarcade);

        $this->playDynarcadeAction->execute();

        $I->assertEquals(2, $gamerPlayer->getActionPoint());
        $I->assertEquals(5, $gamerPlayer->getHealthPoint());
        $I->assertEquals(7, $gamerPlayer->getMoralPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $aplhaBay2->getName(),
            'playerInfo' => $gamerPlayer->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::PLAY_ARCADE_FAIL,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    private function createAction(int $succesRate): Action
    {
        $action = new Action();
        $action
            ->setActionName(ActionEnum::PLAY_ARCADE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(1)
            ->setSuccessRate($succesRate)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::PRIVATE)
            ->buildName(GameConfigEnum::TEST);
        return $action;
    }
}
