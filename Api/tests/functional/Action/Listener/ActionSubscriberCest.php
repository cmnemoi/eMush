<?php

namespace Mush\Tests\functional\Action\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Listener\ActionSubscriber;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class ActionSubscriberCest extends AbstractFunctionalTest
{
    private ActionSubscriber $actionSubscriber;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->actionSubscriber = $I->grabService(ActionSubscriber::class);
    }

    public function testOnPostActionSubscriberInjury(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(2)
        ;
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $action = new Action();
        $action
            ->setInjuryRate(100)
            ->setActionName(ActionEnum::TAKE)
            ->buildName(GameConfigEnum::TEST)
        ;

        $actionEvent = new ActionEvent($action, $player, null);

        // Test injury
        $this->actionSubscriber->onPostAction($actionEvent);

        $I->assertEquals(8, $player->getHealthPoint());
        $I->assertCount(0, $player->getStatuses());
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => PlayerModifierLogEnum::CLUMSINESS,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testOnPostActionSubscriberDirty(FunctionalTester $I)
    {
        $dirtyConfig = new StatusConfig();
        $dirtyConfig
            ->setStatusName(PlayerStatusEnum::DIRTY)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($dirtyConfig);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$dirtyConfig]))
        ;
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(2)
        ;
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $action = new Action();
        $action
            ->setDirtyRate(100)
            ->setActionName(ActionEnum::TAKE)
            ->buildName(GameConfigEnum::TEST)
        ;

        $actionEvent = new ActionEvent($action, $player, null);

        // Test dirty
        $this->actionSubscriber->onPostAction($actionEvent);

        $I->assertEquals(10, $player->getHealthPoint());
        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(PlayerStatusEnum::DIRTY, $player->getStatuses()->first()->getName());
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => StatusEventLogEnum::SOILED,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testOnPostActionSubscriberAlreadyDirty(FunctionalTester $I)
    {
        $dirtyConfig = new StatusConfig();
        $dirtyConfig
            ->setStatusName(PlayerStatusEnum::DIRTY)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($dirtyConfig);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$dirtyConfig]))
        ;
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(2)
        ;
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $action = new Action();
        $action
            ->setDirtyRate(100)
            ->setActionName(ActionEnum::TAKE)
            ->buildName(GameConfigEnum::TEST)
        ;

        $dirty = new Status($player, $dirtyConfig);
        $I->haveInRepository($dirty);

        $actionEvent = new ActionEvent($action, $player, null);

        // Test already dirty
        $this->actionSubscriber->onPostAction($actionEvent);

        $I->assertEquals(10, $player->getHealthPoint());
        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(PlayerStatusEnum::DIRTY, $player->getStatuses()->first()->getName());
    }

    public function testOnPostActionSubscriberDirtyApron(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(2)
        ;
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $action = new Action();
        $action
            ->setDirtyRate(100)
            ->setActionName(ActionEnum::TAKE)
        ;

        $actionEvent = new ActionEvent($action, $player, null);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, ['name' => GearItemEnum::STAINPROOF_APRON]);

        //       $gear = new Gear();
        $modifierConfig = $I->grabEntityFromRepository(EventModifierConfig::class, [
            'modifierName' => ModifierNameEnum::APRON_MODIFIER,
        ]);

        $modifier = new GameModifier($player, $modifierConfig);
        $I->refreshEntities($player);
        $I->haveInRepository($modifier);

        // Test dirty with apron
        $this->actionSubscriber->onPostAction($actionEvent);

        $I->assertEquals(10, $player->getHealthPoint());
        $I->assertCount(0, $player->getStatuses());
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => LogEnum::SOIL_PREVENTED,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testHandlePatrolShipDamageDestroyPatrolShipIfNoMoreArmorAndPlayerIfNoSpaceSuit(FunctionalTester $I): void
    {
        $pasiphaeRoom = $this->createExtraPlace(RoomEnum::PASIPHAE, $I, $this->daedalus);

        $this->player1->setPlace($pasiphaeRoom);

        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::PASIPHAE));
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig)
        ;
        $I->haveInRepository($pasiphae);

        /** @var ChargeStatusConfig $pasiphaeArmorConfig */
        $pasiphaeArmorConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_pasiphae_default']);
        $pasiphaeArmor = new ChargeStatus($pasiphae, $pasiphaeArmorConfig);
        $pasiphaeArmor->setCharge(1);
        $I->haveInRepository($pasiphaeArmor);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::LAND)
            ->setCriticalRate(100)
        ;
        $actionEvent = new ActionEvent($action, $this->player1, $pasiphae);
        $actionEvent->setActionResult(new Fail());

        $this->actionSubscriber->onPostAction($actionEvent);

        $I->dontSeeInRepository(GameEquipment::class, [
            'name' => EquipmentEnum::PASIPHAE,
        ]);
        $I->assertEquals($this->daedalus->getSpace()->getName(), $this->player1->getPlace()->getName());
        $I->assertFalse($this->player1->isAlive());
    }

    public function testHandlePatrolShipDamageDestroyPatrolShipIfNoMoreArmorButNotPlayerIfHasSpaceSuit(FunctionalTester $I): void
    {
        $pasiphaeRoom = $this->createExtraPlace(RoomEnum::PASIPHAE, $I, $this->daedalus);
        $this->player1->setPlace($pasiphaeRoom);

        $spacesuitConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => GearItemEnum::SPACESUIT]);
        $spacesuit = new GameItem($this->player1);
        $spacesuit
            ->setName(GearItemEnum::SPACESUIT)
            ->setEquipment($spacesuitConfig)
        ;
        $I->haveInRepository($spacesuit);

        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::PASIPHAE));
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig)
        ;
        $I->haveInRepository($pasiphae);

        /** @var ChargeStatusConfig $pasiphaeArmorConfig */
        $pasiphaeArmorConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_pasiphae_default']);
        $pasiphaeArmor = new ChargeStatus($pasiphae, $pasiphaeArmorConfig);
        $pasiphaeArmor->setCharge(1);
        $I->haveInRepository($pasiphaeArmor);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::LAND)
            ->setCriticalRate(100)
        ;
        $actionEvent = new ActionEvent($action, $this->player1, $pasiphae);
        $actionEvent->setActionResult(new Fail());

        $this->actionSubscriber->onPostAction($actionEvent);

        $I->dontSeeInRepository(GameEquipment::class, [
            'name' => EquipmentEnum::PASIPHAE,
        ]);
        $I->assertEquals($this->daedalus->getSpace()->getName(), $this->player1->getPlace()->getName());
        $I->assertTrue($this->player1->isAlive());
    }

    public function testHandlePatrolShipDamagePatrolShipItemsInSpaceAtDestruction(FunctionalTester $I): void
    {
        $pasiphaeRoom = $this->createExtraPlace(RoomEnum::PASIPHAE, $I, $this->daedalus);
        $this->player1->setPlace($pasiphaeRoom);

        // give some items to player and pasiphae : they should land in space at pasiphae destruction
        $oldShirtConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => ItemEnum::OLD_T_SHIRT]);
        $playerShirt = new GameItem($this->player1);
        $playerShirt
            ->setName(ItemEnum::OLD_T_SHIRT)
            ->setEquipment($oldShirtConfig)
        ;
        $I->haveInRepository($playerShirt);

        $pasiphaeShirt = new GameItem($pasiphaeRoom);
        $pasiphaeShirt
            ->setName(ItemEnum::OLD_T_SHIRT)
            ->setEquipment($oldShirtConfig)
        ;
        $I->haveInRepository($pasiphaeShirt);

        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::PASIPHAE));
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig)
        ;
        $I->haveInRepository($pasiphae);

        /** @var ChargeStatusConfig $pasiphaeArmorConfig */
        $pasiphaeArmorConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_pasiphae_default']);
        $pasiphaeArmor = new ChargeStatus($pasiphae, $pasiphaeArmorConfig);
        $pasiphaeArmor->setCharge(1);
        $I->haveInRepository($pasiphaeArmor);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::LAND)
            ->setCriticalRate(100)
        ;
        $actionEvent = new ActionEvent($action, $this->player1, $pasiphae);
        $actionEvent->setActionResult(new Fail());

        $this->actionSubscriber->onPostAction($actionEvent);

        $I->assertTrue($this->daedalus->getSpace()->hasEquipmentByName(ItemEnum::OLD_T_SHIRT));
        $I->assertCount(2, $this->daedalus->getSpace()->getEquipments());
    }
}
