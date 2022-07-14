<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\PublicBroadcast;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PublicBroadcastCest
{
    private PublicBroadcast $PublicBroadcastAction;

    public function _before(FunctionalTester $I)
    {
        $this->PublicBroadcastAction = $I->grabService(PublicBroadcast::class);
        $this->eventDispatcherService = $I->grabService(EventDispatcherInterface::class);
    }

    public function testPublicBroadcast(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'roomName']);

        $watchedPublicBroadcastStatus = new ChargeStatusConfig();
        $watchedPublicBroadcastStatus
            ->setName(PlayerStatusEnum::WATCHED_PUBLIC_BROADCAST)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::NONE)
            ->setStartCharge(1)
            ->setGameConfig($gameConfig)
        ;

        $I->haveInRepository($watchedPublicBroadcastStatus);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(2)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::PUBLIC_BROADCAST)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost($actionCost);
        $I->haveInRepository($action);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class);
        $itemConfig
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::ALIEN_HOLOGRAPHIC_TV)
            ->setActions(new ArrayCollection([$action]))
        ;

        $gameItem = new GameItem();
        $gameItem
            ->setName(ToolItemEnum::ALIEN_HOLOGRAPHIC_TV)
            ->setEquipment($itemConfig)
            ->setHolder($room)
        ;
        $I->haveInRepository($gameItem);

        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setName(StatusEnum::ATTEMPT)
            ->setGameConfig($gameConfig)
            ->setVisibility(VisibilityEnum::HIDDEN)
        ;
        $I->haveInRepository($attemptConfig);

        /** @var CharacterConfig $characterConfig */
        $player1Config = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN,
            'actions' => new ArrayCollection([$action]),
        ]);

        $player2Config = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player1 = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
            'characterConfig' => $player1Config,
        ]);

        /** @var Player $targetPlayer */
        $player2 = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
            'characterConfig' => $player2Config,
        ]);

        $this->PublicBroadcastAction->loadParameters($action, $player1, $gameItem);

        $I->assertTrue($this->PublicBroadcastAction->isVisible());
        $I->assertNull($this->PublicBroadcastAction->cannotExecuteReason());

        $this->PublicBroadcastAction->execute();

        $I->assertEquals(8, $player1->getActionPoint());
        $I->assertEquals(9, $player1->getMoralPoint());

        $I->assertEquals(10, $player2->getActionPoint());
        $I->assertEquals(9, $player2->getMoralPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player1->getId(),
            'log' => ActionLogEnum::PUBLIC_BROADCAST,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testPublicBroadcastAlreadyWatched(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'roomName']);

        $watchedPublicBroadcastStatus = new ChargeStatusConfig();
        $watchedPublicBroadcastStatus
            ->setName(PlayerStatusEnum::WATCHED_PUBLIC_BROADCAST)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::NONE)
            ->setStartCharge(1)
            ->setGameConfig($gameConfig)
        ;

        $I->haveInRepository($watchedPublicBroadcastStatus);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(2)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::PUBLIC_BROADCAST)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost($actionCost);
        $I->haveInRepository($action);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class);
        $itemConfig
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::ALIEN_HOLOGRAPHIC_TV)
            ->setActions(new ArrayCollection([$action]))
        ;

        $gameItem = new GameItem();
        $gameItem
            ->setName(ToolItemEnum::ALIEN_HOLOGRAPHIC_TV)
            ->setEquipment($itemConfig)
            ->setHolder($room)
        ;
        $I->haveInRepository($gameItem);

        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setName(StatusEnum::ATTEMPT)
            ->setGameConfig($gameConfig)
            ->setVisibility(VisibilityEnum::HIDDEN)
        ;
        $I->haveInRepository($attemptConfig);

        /** @var CharacterConfig $characterConfig */
        $player1Config = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN,
            'actions' => new ArrayCollection([$action]),
        ]);

        $player2Config = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player1 = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
            'characterConfig' => $player1Config,
        ]);

        /** @var Player $targetPlayer */
        $player2 = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
            'characterConfig' => $player2Config,
        ]);

        $this->PublicBroadcastAction->loadParameters($action, $player1, $gameItem);

        $I->assertTrue($this->PublicBroadcastAction->isVisible());
        $I->assertNull($this->PublicBroadcastAction->cannotExecuteReason());

        $this->PublicBroadcastAction->execute();
        $this->PublicBroadcastAction->execute();

        $I->assertEquals(6, $player1->getActionPoint());
        $I->assertEquals(9, $player1->getMoralPoint());

        $I->assertEquals(10, $player2->getActionPoint());
        $I->assertEquals(9, $player2->getMoralPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player1->getId(),
            'log' => ActionLogEnum::PUBLIC_BROADCAST,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
