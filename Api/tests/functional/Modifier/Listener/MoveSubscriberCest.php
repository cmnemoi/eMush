<?php

namespace functional\Modifier\Listener;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Drop;
use Mush\Action\Actions\Move;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

class MoveSubscriberCest
{
    private Drop $dropAction;

    public function _before(FunctionalTester $I)
    {
        $this->dropAction = $I->grabService(Move::class);
    }

//    public function testDropGearWithPlayerReach(FunctionalTester $I)
//    {
//        /** @var GameConfig $gameConfig */
//        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);
//
//        /** @var Daedalus $daedalus */
//        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
//        /** @var Place $room */
//        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
//
//        /** @var CharacterConfig $characterConfig */
//        $characterConfig = $I->have(CharacterConfig::class);
//        /** @var Player $player */
//        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);
//
//        $actionCost = new ActionCost();
//        $I->haveInRepository($actionCost);
//
//        $takeActionEntity = new Action();
//        $takeActionEntity
//            ->setName(ActionEnum::DROP)
//            ->setDirtyRate(0)
//            ->setScope(ActionScopeEnum::CURRENT)
//            ->setInjuryRate(0)
//            ->setActionCost($actionCost)
//        ;
//        $I->haveInRepository($takeActionEntity);
//
//        $modifierConfig = new ModifierConfig();
//        $modifierConfig
//            ->setScope(ActionEnum::SHOWER)
//            ->setTarget(ModifierTargetEnum::ACTION_POINT)
//            ->setDelta(-1)
//            ->setReach(ModifierReachEnum::PLAYER)
//            ->setMode(ModifierModeEnum::ADDITIVE)
//            ->setGameConfig($gameConfig)
//        ;
//        $I->haveInRepository($modifierConfig);
//
//        $I->refreshEntities($player);
//        $modifier = new Modifier($player, $modifierConfig);
//        $I->haveInRepository($modifier);
//
//        $gear = new Gear();
//        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig]));
//        $I->haveInRepository($gear);
//
//        /** @var EquipmentConfig $equipmentConfig */
//        $equipmentConfig = $I->have(EquipmentConfig::class, [
//            'gameConfig' => $gameConfig,
//            'mechanics' => new ArrayCollection([$gear]),
//            'actions' => new ArrayCollection([$takeActionEntity]),
//        ]);
//
//        //Case of a game Equipment
//        $gameEquipment = new GameItem();
//        $gameEquipment
//            ->setEquipment($equipmentConfig)
//            ->setName('some name')
//            ->setPlayer($player)
//        ;
//        $I->haveInRepository($gameEquipment);
//
//        $I->refreshEntities($player);
//        $player->addItem($gameEquipment);
//        $I->refreshEntities($player);
//
//        $this->dropAction->loadParameters($takeActionEntity, $player, $gameEquipment);
//        $this->dropAction->execute();
//
//        $I->assertEquals($room->getEquipments()->count(), 1);
//        $I->assertEquals($player->getItems()->count(), 0);
//        $I->assertEquals($player->getModifiers()->count(), 0);
//        $I->assertEquals($room->getModifiers()->count(), 0);
//    }
}
