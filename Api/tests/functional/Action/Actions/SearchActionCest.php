<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Hide;
use Mush\Action\Actions\Search;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class SearchActionCest
{
    private Search $searchAction;
    private Hide $hideAction;

    public function _before(FunctionalTester $I)
    {
        $this->searchAction = $I->grabService(Search::class);
        $this->hideAction = $I->grabService(Hide::class);
    }

    public function testSearch(FunctionalTester $I)
    {
        $attemptConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['statusName' => StatusEnum::ATTEMPT]);

        $hiddenConfig = new StatusConfig();
        $hiddenConfig->setStatusName(EquipmentStatusEnum::HIDDEN)->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($hiddenConfig);

        $searchAction = new ActionConfig();
        $searchAction
            ->setActionName(ActionEnum::SEARCH)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setActionCost(1)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($searchAction);

        $hideAction = new ActionConfig();
        $hideAction
            ->setActionName(ActionEnum::HIDE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($searchAction);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$attemptConfig, $hiddenConfig]));
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['actions' => new ArrayCollection([$searchAction])]);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(2)
            ->setHealthPoint(6);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(16);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var ItemConfig $equipmentConfig */
        $equipmentConfig = $I->have(ItemConfig::class, [
            'actions' => new ArrayCollection([$searchAction]),
        ]);

        $gameEquipment1 = new GameItem($room);
        $gameEquipment1
            ->setEquipment($equipmentConfig)
            ->setName('equipment1');
        $I->haveInRepository($gameEquipment1);

        // first search an room without any hidden equipment
        $this->searchAction->loadParameters(
            actionConfig: $searchAction,
            actionProvider: $player,
            player: $player);
        $result = $this->searchAction->execute();
        $I->assertInstanceOf(Fail::class, $result);

        // Now hide an equipment
        $this->hideAction->loadParameters(
            actionConfig: $searchAction,
            actionProvider: $gameEquipment1,
            player: $player,
            target: $gameEquipment1);
        $this->hideAction->execute();
        $I->assertCount(1, $room->getEquipments());

        $hiddenStatus = $gameEquipment1->getStatuses()->first();

        $I->assertNotNull($hiddenStatus);
        $I->assertEquals($hiddenStatus->getName(), EquipmentStatusEnum::HIDDEN);
        $I->assertEquals($hiddenStatus->getOwner(), $gameEquipment1);
        $I->assertEquals($hiddenStatus->getTarget(), $player);

        // Now search again
        $this->searchAction->loadParameters(
            actionConfig: $searchAction,
            actionProvider: $player,
            player: $player);
        $result = $this->searchAction->execute();
        $I->assertInstanceOf(Success::class, $result);

        $I->assertEmpty($gameEquipment1->getStatuses());
    }

    public function testSearchSeveralHidenEquipments(FunctionalTester $I)
    {
        $attemptConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['statusName' => StatusEnum::ATTEMPT]);

        $hiddenConfig = new StatusConfig();
        $hiddenConfig->setStatusName(EquipmentStatusEnum::HIDDEN)->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($hiddenConfig);

        $searchAction = new ActionConfig();
        $searchAction
            ->setActionName(ActionEnum::SEARCH)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setActionCost(1)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($searchAction);

        $hideAction = new ActionConfig();
        $hideAction
            ->setActionName(ActionEnum::HIDE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($searchAction);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$attemptConfig, $hiddenConfig]));
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['actions' => new ArrayCollection([$searchAction])]);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(2)
            ->setHealthPoint(6);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(16);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player2->setPlayerVariables($characterConfig);
        $player2
            ->setActionPoint(16);
        $playerInfo2 = new PlayerInfo($player2, $user, $characterConfig);
        $I->haveInRepository($playerInfo2);
        $player2->setPlayerInfo($playerInfo2);
        $I->refreshEntities($player2);

        /** @var ItemConfig $equipmentConfig */
        $equipmentConfig = $I->have(ItemConfig::class, [
            'actions' => new ArrayCollection([$searchAction]),
        ]);

        $gameEquipment1 = new GameItem($room);
        $gameEquipment1
            ->setEquipment($equipmentConfig)
            ->setName('equipment1');
        $I->haveInRepository($gameEquipment1);
        $gameEquipment2 = new GameItem($room);
        $gameEquipment2
            ->setEquipment($equipmentConfig)
            ->setName('equipment2');
        $I->haveInRepository($gameEquipment2);
        $gameEquipment3 = new GameItem($room);
        $gameEquipment3
            ->setEquipment($equipmentConfig)
            ->setName('equipment3');
        $I->haveInRepository($gameEquipment3);

        // Now hide equipments
        $this->hideAction->loadParameters(
            actionConfig: $searchAction,
            actionProvider: $gameEquipment1,
            player: $player,
            target: $gameEquipment1);
        $this->hideAction->execute();
        $this->hideAction->loadParameters(
            actionConfig: $searchAction,
            actionProvider: $gameEquipment1,
            player: $player,
            target: $gameEquipment3);
        $this->hideAction->execute();
        $this->hideAction->loadParameters(
            actionConfig: $searchAction,
            actionProvider: $gameEquipment1,
            player: $player,
            target: $gameEquipment2);
        $this->hideAction->execute();

        $I->assertCount(3, $room->getEquipments());
        $I->assertCount(1, $gameEquipment1->getStatuses());
        $I->assertCount(1, $gameEquipment2->getStatuses());
        $I->assertCount(1, $gameEquipment3->getStatuses());

        // Now search
        $this->searchAction->loadParameters(
            actionConfig: $searchAction,
            actionProvider: $player,
            player: $player);
        $result = $this->searchAction->execute();
        $I->assertCount(1, $gameEquipment1->getStatuses());
        $I->assertCount(0, $gameEquipment2->getStatuses());
        $I->assertCount(1, $gameEquipment3->getStatuses());

        // One more time
        $this->searchAction->loadParameters(
            actionConfig: $searchAction,
            actionProvider: $player,
            player: $player);
        $result = $this->searchAction->execute();
        $I->assertCount(1, $gameEquipment1->getStatuses());
        $I->assertCount(0, $gameEquipment2->getStatuses());
        $I->assertCount(0, $gameEquipment3->getStatuses());

        // Third time
        $this->searchAction->loadParameters(
            actionConfig: $searchAction,
            actionProvider: $player,
            player: $player);
        $result = $this->searchAction->execute();
        $I->assertCount(0, $gameEquipment1->getStatuses());
        $I->assertCount(0, $gameEquipment2->getStatuses());
        $I->assertCount(0, $gameEquipment3->getStatuses());
    }
}
