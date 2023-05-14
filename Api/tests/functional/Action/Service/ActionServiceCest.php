<?php

namespace functional\Action\Service;

use App\Tests\FunctionalTester;
use Mush\Action\Entity\Action;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Action\Service\ActionService;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\User\Entity\User;

class ActionServiceCest
{
    private ActionServiceInterface $actionService;

    public function _before(FunctionalTester $I)
    {
        $this->actionService = $I->grabService(ActionService::class);
    }

    public function testApplyCostToPlayer(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $room,
            'daedalus' => $daedalus,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(10)
        ;

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $action = new Action();
        $action
            ->setActionName('some name')
            ->setActionCost(6)
        ;

        $this->actionService->applyCostToPlayer($player, $action, null);

        $I->assertEquals(4, $player->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalus->getDaedalusInfo(),
            'playerInfo' => $player->getPlayerInfo(),
            'visibility' => VisibilityEnum::HIDDEN,
        ]);
    }

    public function testApplyCostToPlayerFreeAction(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $room,
            'daedalus' => $daedalus,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(10)
        ;

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $action = new Action();
        $action
            ->setActionName('some name')
            ->setActionCost(0)
        ;

        $this->actionService->applyCostToPlayer($player, $action, null);

        $I->assertEquals(10, $player->getActionPoint());
    }

    public function testApplyCostToPlayerWithMovementPointConversion(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $room,
            'daedalus' => $daedalus,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(10)
            ->setMovementPoint(0)
        ;
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $action = new Action();
        $action->setActionName('some name');
        $action->setMovementCost(1);

        $this->actionService->applyCostToPlayer($player, $action, null);

        $I->assertEquals(9, $player->getActionPoint());
        $I->assertEquals(1, $player->getMovementPoint());
    }

    public function testApplyCostToPlayerWithMovementPointConversionAndModifier(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $room,
            'daedalus' => $daedalus,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(10)
            ->setMovementPoint(0)
        ;

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $modifierConfig = new VariableEventModifierConfig('movementConversionModifier_test');
        $modifierConfig
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([ActionVariableEvent::MOVEMENT_CONVERSION => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
        ;

        $I->haveInRepository($modifierConfig);

        $disabledModifier = new GameModifier($player, $modifierConfig);

        $I->haveInRepository($disabledModifier);

        $action = new Action();
        $action->setActionName('some name');
        $action->setMovementCost(1);

        $this->actionService->applyCostToPlayer($player, $action, null);

        $I->assertEquals(9, $player->getActionPoint());
        $I->assertEquals(0, $player->getMovementPoint());
    }
}
