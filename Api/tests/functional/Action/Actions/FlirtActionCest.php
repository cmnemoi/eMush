<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Flirt;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\StatusEnum;

class FlirtActionCest
{
    private Flirt $flirtAction;

    public function _before(FunctionalTester $I)
    {
        $this->flirtAction = $I->grabService(Flirt::class);
    }

    public function testFlirt(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'gameStatus' => GameStatusEnum::CURRENT]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
        ;
        $I->haveInRepository($actionCost);

        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setName(StatusEnum::ATTEMPT)
            ->setGameConfig($gameConfig)
            ->setVisibility(VisibilityEnum::HIDDEN)
        ;
        $I->haveInRepository($attemptConfig);

        $action = new Action();
        $action
            ->setName(ActionEnum::FLIRT)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setActionCost($actionCost);
        $I->haveInRepository($actionCost);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['name' => CharacterEnum::DEREK, 'actions' => new ArrayCollection([$action])]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
            'characterConfig' => $characterConfig,
        ]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['name' => CharacterEnum::CHUN, 'actions' => new ArrayCollection([$action])]);
        /** @var Player $player */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
            'characterConfig' => $characterConfig,
        ]);

        $this->flirtAction->loadParameters($action, $player, $targetPlayer);

        $I->assertTrue($this->flirtAction->isVisible());
        $I->assertNull($this->flirtAction->cannotExecuteReason());

        $this->flirtAction->execute();

        $I->assertEquals(1, $player->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => ActionLogEnum::FLIRT_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->assertTrue($player->HasFlirtedWith($targetPlayer));
        $I->assertFalse($targetPlayer->HasFlirtedWith($player));
    }

    public function testCoupleOfMenFlirt(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'gameStatus' => GameStatusEnum::CURRENT]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
        ;
        $I->haveInRepository($actionCost);

        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setName(StatusEnum::ATTEMPT)
            ->setGameConfig($gameConfig)
            ->setVisibility(VisibilityEnum::HIDDEN)
        ;
        $I->haveInRepository($attemptConfig);

        $action = new Action();
        $action
            ->setName(ActionEnum::FLIRT)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setActionCost($actionCost);
        $I->haveInRepository($actionCost);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['name' => CharacterEnum::CHAO, 'actions' => new ArrayCollection([$action])]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
            'characterConfig' => $characterConfig,
        ]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['name' => CharacterEnum::DEREK, 'actions' => new ArrayCollection([$action])]);
        /** @var Player $player */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
            'characterConfig' => $characterConfig,
        ]);

        $this->flirtAction->loadParameters($action, $player, $targetPlayer);

        $I->assertFalse($this->flirtAction->isVisible());
    }

    public function testCoupleOfWomenFlirt(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'gameStatus' => GameStatusEnum::CURRENT]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
        ;
        $I->haveInRepository($actionCost);

        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setName(StatusEnum::ATTEMPT)
            ->setGameConfig($gameConfig)
            ->setVisibility(VisibilityEnum::HIDDEN)
        ;
        $I->haveInRepository($attemptConfig);

        $action = new Action();
        $action
            ->setName(ActionEnum::FLIRT)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setActionCost($actionCost);
        $I->haveInRepository($actionCost);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['name' => CharacterEnum::CHUN, 'actions' => new ArrayCollection([$action])]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
            'characterConfig' => $characterConfig,
        ]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['name' => CharacterEnum::PAOLA, 'actions' => new ArrayCollection([$action])]);
        /** @var Player $player */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
            'characterConfig' => $characterConfig,
        ]);

        $this->flirtAction->loadParameters($action, $player, $targetPlayer);

        $I->assertFalse($this->flirtAction->isVisible());
    }

    public function testAndieAndWomanFlirt(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'gameStatus' => GameStatusEnum::CURRENT]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
        ;
        $I->haveInRepository($actionCost);

        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setName(StatusEnum::ATTEMPT)
            ->setGameConfig($gameConfig)
            ->setVisibility(VisibilityEnum::HIDDEN)
        ;
        $I->haveInRepository($attemptConfig);

        $action = new Action();
        $action
            ->setName(ActionEnum::FLIRT)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setActionCost($actionCost);
        $I->haveInRepository($actionCost);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['name' => CharacterEnum::ANDIE, 'actions' => new ArrayCollection([$action])]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
            'characterConfig' => $characterConfig,
        ]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['name' => CharacterEnum::CHUN, 'actions' => new ArrayCollection([$action])]);
        /** @var Player $player */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
            'characterConfig' => $characterConfig,
        ]);

        $this->flirtAction->loadParameters($action, $player, $targetPlayer);

        $I->assertTrue($this->flirtAction->isVisible());
        $I->assertNull($this->flirtAction->cannotExecuteReason());

        $this->flirtAction->execute();

        $I->assertEquals(1, $player->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => ActionLogEnum::FLIRT_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->assertTrue($player->HasFlirtedWith($targetPlayer));
        $I->assertFalse($targetPlayer->HasFlirtedWith($player));
    }

    public function testAndieAndManFlirt(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'gameStatus' => GameStatusEnum::CURRENT]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
        ;
        $I->haveInRepository($actionCost);

        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setName(StatusEnum::ATTEMPT)
            ->setGameConfig($gameConfig)
            ->setVisibility(VisibilityEnum::HIDDEN)
        ;
        $I->haveInRepository($attemptConfig);

        $action = new Action();
        $action
            ->setName(ActionEnum::FLIRT)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setActionCost($actionCost);
        $I->haveInRepository($actionCost);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['name' => CharacterEnum::ANDIE, 'actions' => new ArrayCollection([$action])]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
            'characterConfig' => $characterConfig,
        ]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['name' => CharacterEnum::DEREK, 'actions' => new ArrayCollection([$action])]);
        /** @var Player $player */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
            'characterConfig' => $characterConfig,
        ]);

        $this->flirtAction->loadParameters($action, $player, $targetPlayer);

        $I->assertTrue($this->flirtAction->isVisible());
        $I->assertNull($this->flirtAction->cannotExecuteReason());

        $this->flirtAction->execute();

        $I->assertEquals(1, $player->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => ActionLogEnum::FLIRT_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->assertTrue($player->HasFlirtedWith($targetPlayer));
        $I->assertFalse($targetPlayer->HasFlirtedWith($player));
    }
}
