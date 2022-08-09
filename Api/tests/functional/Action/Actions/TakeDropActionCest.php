<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Drop;
use Mush\Action\Actions\Hide;
use Mush\Action\Actions\Take;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;

class TakeDropActionCest
{
    private Take $takeAction;
    private Drop $dropAction;
    private Hide $hideAction;

    public function _before(FunctionalTester $I)
    {
        $this->takeAction = $I->grabService(Take::class);
        $this->dropAction = $I->grabService(Drop::class);
        $this->hideAction = $I->grabService(Hide::class);
    }

    public function testTakeDropItem(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
            'characterConfig' => $characterConfig,
        ]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(0)
            ->setMovementPointCost(0)
            ->setMoralPointCost(0);

        $actionTake = new Action();
        $actionTake
            ->setName(ActionEnum::TAKE)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $actionDrop = new Action();
        $actionDrop
            ->setName(ActionEnum::DROP)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($actionCost);
        $I->haveInRepository($actionTake);
        $I->haveInRepository($actionDrop);

        /** @var EquipmentConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, ['actions' => new ArrayCollection([$actionTake, $actionDrop])]);

        $gameItem = new GameItem();
        $gameItem
            ->setEquipment($itemConfig)
            ->setName('shower')
            ->setHolder($room)
        ;
        $I->haveInRepository($gameItem);

        // first let's test take action
        $this->takeAction->loadParameters($actionTake, $player, $gameItem);
        $this->dropAction->loadParameters($actionDrop, $player, $gameItem);

        $I->assertTrue($this->takeAction->isVisible());
        $I->assertFalse($this->dropAction->isVisible());
        $I->assertNull($this->takeAction->cannotExecuteReason());

        $this->takeAction->execute();

        $I->assertCount(1, $player->getEquipments());
        $I->assertCount(0, $room->getEquipments());
        $I->assertCount(0, $player->getStatuses());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => ActionLogEnum::TAKE,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        // drop Action
        $this->takeAction->loadParameters($actionTake, $player, $gameItem);
        $this->dropAction->loadParameters($actionDrop, $player, $gameItem);

        $I->assertTrue($this->dropAction->isVisible());
        $I->assertFalse($this->takeAction->isVisible());
        $I->assertNull($this->dropAction->cannotExecuteReason());

        $this->dropAction->execute();

        $I->assertCount(0, $player->getEquipments());
        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(0, $player->getStatuses());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => ActionLogEnum::DROP,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function TakeDropHeavyItem(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
            'characterConfig' => $characterConfig,
        ]);

        $burdenedStatusConfig = new StatusConfig();
        $burdenedStatusConfig->setName(PlayerStatusEnum::BURDENED)->setGameConfig($gameConfig);
        $I->haveInRepository($burdenedStatusConfig);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(0)
            ->setMovementPointCost(0)
            ->setMoralPointCost(0);

        $actionTake = new Action();
        $actionTake
            ->setName(ActionEnum::TAKE)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $actionDrop = new Action();
        $actionDrop
            ->setName(ActionEnum::DROP)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($actionCost);
        $I->haveInRepository($actionTake);
        $I->haveInRepository($actionDrop);

        /** @var EquipmentConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, ['actions' => new ArrayCollection([$actionTake, $actionDrop])]);

        $gameItem = new GameItem();
        $gameItem
            ->setEquipment($itemConfig)
            ->setName('shower')
            ->setHolder($room)
        ;
        $I->haveInRepository($gameItem);

        $heavyConfig = new StatusConfig();
        $heavyConfig->setName(EquipmentStatusEnum::HEAVY)->setGameConfig($gameConfig);
        $I->haveInRepository($heavyConfig);
        $heavyStatus = new Status($gameItem, $heavyConfig);
        $I->haveInRepository($heavyStatus);

        // first let's test take action
        $this->takeAction->loadParameters($actionTake, $player, $gameItem);
        $this->dropAction->loadParameters($actionDrop, $player, $gameItem);

        $I->assertTrue($this->takeAction->isVisible());
        $I->assertFalse($this->dropAction->isVisible());
        $I->assertNull($this->takeAction->cannotExecuteReason());

        $this->takeAction->execute();

        $I->assertCount(1, $player->getEquipments());
        $I->assertCount(0, $room->getEquipments());
        $I->assertCount(1, $player->getStatuses());
        $I->assertCount(1, $gameItem->getStatuses());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => ActionLogEnum::TAKE,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        // drop Action
        $this->takeAction->loadParameters($actionTake, $player, $gameItem);
        $this->dropAction->loadParameters($actionDrop, $player, $gameItem);

        $I->assertTrue($this->dropAction->isVisible());
        $I->assertFalse($this->takeAction->isVisible());
        $I->assertNull($this->dropAction->cannotExecuteReason());

        $this->dropAction->execute();

        $I->assertCount(0, $player->getEquipments());
        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(0, $player->getStatuses());
        $I->assertCount(1, $gameItem->getStatuses());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => ActionLogEnum::DROP,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function TakeHiddenItem(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
            'characterConfig' => $characterConfig,
        ]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(0)
            ->setMovementPointCost(0)
            ->setMoralPointCost(0);

        $actionTake = new Action();
        $actionTake
            ->setName(ActionEnum::TAKE)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;

        $I->haveInRepository($actionCost);
        $I->haveInRepository($actionTake);

        /** @var EquipmentConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, ['actions' => new ArrayCollection([$actionTake])]);

        $gameItem = new GameItem();
        $gameItem
            ->setEquipment($itemConfig)
            ->setName('shower')
            ->setHolder($room)
        ;
        $I->haveInRepository($gameItem);

        $hiddenConfig = new StatusConfig();
        $hiddenConfig->setName(EquipmentStatusEnum::HIDDEN)->setGameConfig($gameConfig);
        $I->haveInRepository($hiddenConfig);
        $hiddenStatus = new Status($gameItem, $hiddenConfig);
        $hiddenStatus->setTarget($player);
        $I->haveInRepository($hiddenStatus);

        // Take action
        $this->takeAction->loadParameters($actionTake, $player, $gameItem);

        $I->assertTrue($this->takeAction->isVisible());
        $I->assertNull($this->takeAction->cannotExecuteReason());

        $this->takeAction->execute();

        $I->assertCount(1, $player->getEquipments());
        $I->assertCount(0, $room->getEquipments());
        $I->assertCount(0, $player->getStatuses());
        $I->assertCount(0, $gameItem->getStatuses());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => ActionLogEnum::TAKE,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function HideHeavyItemInInventory(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
            'characterConfig' => $characterConfig,
        ]);

        $hideStatusConfig = new StatusConfig();
        $hideStatusConfig->setName(EquipmentStatusEnum::HIDDEN)->setGameConfig($gameConfig);
        $I->haveInRepository($hideStatusConfig);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(0)
            ->setMovementPointCost(0)
            ->setMoralPointCost(0);

        $actionHide = new Action();
        $actionHide
            ->setName(ActionEnum::HIDE)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($actionCost);
        $I->haveInRepository($actionHide);

        /** @var EquipmentConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, ['actions' => new ArrayCollection([$actionHide])]);

        $gameItem = new GameItem();
        $gameItem
            ->setEquipment($itemConfig)
            ->setName('shower')
            ->setHolder($player)
        ;
        $I->haveInRepository($gameItem);

        $burdenedStatusConfig = new StatusConfig();
        $burdenedStatusConfig->setName(PlayerStatusEnum::BURDENED)->setGameConfig($gameConfig);
        $I->haveInRepository($burdenedStatusConfig);
        $burdenedStatus = new Status($player, $burdenedStatusConfig);
        $I->haveInRepository($burdenedStatus);
        $heavyConfig = new StatusConfig();

        $heavyConfig->setName(EquipmentStatusEnum::HEAVY)->setGameConfig($gameConfig);
        $I->haveInRepository($heavyConfig);
        $heavyStatus = new Status($gameItem, $heavyConfig);
        $I->haveInRepository($heavyStatus);

        // Take action
        $this->hideAction->loadParameters($actionHide, $player, $gameItem);

        $I->assertTrue($this->hideAction->isVisible());
        $I->assertNull($this->hideAction->cannotExecuteReason());

        $this->hideAction->execute();

        $I->assertCount(0, $player->getEquipments());
        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(2, $gameItem->getStatuses());
        $I->assertEquals($player, $gameItem->getStatusByName(EquipmentStatusEnum::HIDDEN)->getTarget());
        $I->assertCount(0, $player->getStatuses());
    }
}
