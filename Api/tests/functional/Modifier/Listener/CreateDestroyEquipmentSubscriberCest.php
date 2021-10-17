<?php

namespace functional\Modifier\Listener;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\GameItem;
use Mush\Equipment\Entity\Config\Mechanics\Gear;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CreateDestroyEquipmentSubscriberCest
{
    private EventDispatcherInterface $eventDispatcherService;

    public function _before(FunctionalTester $I)
    {
        $this->eventDispatcherService = $I->grabService(EventDispatcherInterface::class);
    }

    public function testCreateGearPlayerScope(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);

        $modifierConfig = new ModifierConfig();
        $modifierConfig
            ->setScope(ActionEnum::SHOWER)
            ->setTarget(ModifierTargetEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $I->haveInRepository($modifierConfig);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig]));
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'mechanics' => new ArrayCollection([$gear])]);

        //Case of a game Equipment
        $gameEquipment = new GameItem();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        $equipmentEvent = new EquipmentEvent(
            $gameEquipment,
            $room,
            VisibilityEnum::PUBLIC,
            ActionEnum::COFFEE,
            new \DateTime()
        );
        $equipmentEvent->setPlayer($player);

        $this->eventDispatcherService->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $I->assertEquals($room->getEquipments()->count(), 0);
        $I->assertEquals($player->getEquipments()->count(), 1);
        $I->assertEquals($player->getModifiers()->count(), 1);
        $I->assertEquals($room->getModifiers()->count(), 0);
        $I->assertEquals($player->getModifiers()->first()->getModifierConfig(), $modifierConfig);
    }

    public function testCreateGearPlayerScopeInventoryFull(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 0]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);

        $modifierConfig = new ModifierConfig();
        $modifierConfig
            ->setScope(ActionEnum::SHOWER)
            ->setTarget(ModifierTargetEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $I->haveInRepository($modifierConfig);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig]));
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'mechanics' => new ArrayCollection([$gear])]);

        //Case of a game Equipment
        $gameEquipment = new GameItem();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        $equipmentEvent = new EquipmentEvent(
            $gameEquipment,
            $room,
            VisibilityEnum::PUBLIC,
            ActionEnum::COFFEE,
            new \DateTime()
        );
        $equipmentEvent->setPlayer($player);

        $this->eventDispatcherService->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $I->assertEquals($room->getEquipments()->count(), 1);
        $I->assertEquals($player->getEquipments()->count(), 0);
        $I->assertEquals($player->getModifiers()->count(), 0);
        $I->assertEquals($room->getModifiers()->count(), 0);
    }

    public function testCreateGearPlaceReach(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);

        $modifierConfig = new ModifierConfig();
        $modifierConfig
            ->setScope(ActionEnum::SHOWER)
            ->setTarget(ModifierTargetEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLACE)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $I->haveInRepository($modifierConfig);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig]));
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'mechanics' => new ArrayCollection([$gear])]);

        //Case of a game Equipment
        $gameEquipment = new GameItem();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        $equipmentEvent = new EquipmentEvent(
            $gameEquipment,
            $room,
            VisibilityEnum::PUBLIC,
            ActionEnum::COFFEE,
            new \DateTime()
        );
        $equipmentEvent->setPlayer($player);

        $this->eventDispatcherService->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $I->assertEquals($room->getEquipments()->count(), 0);
        $I->assertEquals($player->getEquipments()->count(), 1);
        $I->assertEquals($player->getModifiers()->count(), 0);
        $I->assertEquals($room->getModifiers()->count(), 1);
        $I->assertEquals($room->getModifiers()->first()->getModifierConfig(), $modifierConfig);
    }

    public function testDestroyGear(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);

        $actionCost = new ActionCost();
        $I->haveInRepository($actionCost);

        $takeActionEntity = new Action();
        $takeActionEntity
            ->setName(ActionEnum::DROP)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($takeActionEntity);

        $modifierConfig = new ModifierConfig();
        $modifierConfig
            ->setScope(ActionEnum::SHOWER)
            ->setTarget(ModifierTargetEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($modifierConfig);

        $modifier = new Modifier($player, $modifierConfig);
        $I->haveInRepository($modifier);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig]));
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
        ]);

        //Case of a game Equipment
        $gameEquipment = new GameItem();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setHolder($player)
        ;
        $I->haveInRepository($gameEquipment);

        $equipmentEvent = new EquipmentEvent(
            $gameEquipment,
            $room,
            VisibilityEnum::PUBLIC,
            ActionEnum::COFFEE,
            new \DateTime()
        );
        $equipmentEvent->setPlayer($player);

        $this->eventDispatcherService->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $I->assertEquals($room->getEquipments()->count(), 0);
        $I->assertEquals($player->getEquipments()->count(), 0);
        $I->assertEquals($player->getModifiers()->count(), 0);
        $I->assertEquals($room->getModifiers()->count(), 0);
    }

    public function testDestroyOneOfTwoGear(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);

        $actionCost = new ActionCost();
        $I->haveInRepository($actionCost);

        $takeActionEntity = new Action();
        $takeActionEntity
            ->setName(ActionEnum::DROP)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($takeActionEntity);

        $modifierConfig = new ModifierConfig();
        $modifierConfig
            ->setScope(ActionEnum::SHOWER)
            ->setTarget(ModifierTargetEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($modifierConfig);

        $modifier = new Modifier($player, $modifierConfig);
        $I->haveInRepository($modifier);
        $modifier2 = new Modifier($player, $modifierConfig);
        $I->haveInRepository($modifier2);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig]));
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
        ]);

        //Case of a game Equipment
        $gameEquipment = new GameItem();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setHolder($player)
        ;
        $I->haveInRepository($gameEquipment);

        $gameEquipment2 = new GameItem();
        $gameEquipment2
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setHolder($player)
        ;
        $I->haveInRepository($gameEquipment2);

        $equipmentEvent = new EquipmentEvent(
            $gameEquipment,
            $room,
            VisibilityEnum::PUBLIC,
            ActionEnum::COFFEE,
            new \DateTime()
        );
        $equipmentEvent->setPlayer($player);

        $this->eventDispatcherService->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $I->assertEquals($room->getEquipments()->count(), 0);
        $I->assertEquals($player->getEquipments()->count(), 1);
        $I->assertEquals($player->getModifiers()->count(), 1);
        $I->assertEquals($room->getModifiers()->count(), 0);
    }

    public function testTransformGear(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);

        $actionCost = new ActionCost();
        $I->haveInRepository($actionCost);

        $takeActionEntity = new Action();
        $takeActionEntity
            ->setName(ActionEnum::DROP)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($takeActionEntity);

        $modifierConfig = new ModifierConfig();
        $modifierConfig
            ->setScope(ActionEnum::SHOWER)
            ->setTarget(ModifierTargetEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($modifierConfig);

        $modifierConfig2 = new ModifierConfig();
        $modifierConfig2
            ->setScope(ActionEnum::SHOWER)
            ->setTarget(ModifierTargetEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($modifierConfig2);

        $modifier = new Modifier($player, $modifierConfig);
        $I->haveInRepository($modifier);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig]));
        $I->haveInRepository($gear);
        $gear2 = new Gear();
        $gear2->setModifierConfigs(new ArrayCollection([$modifierConfig2]));
        $I->haveInRepository($gear2);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
        ]);
        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig2 = $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear2]),
        ]);

        //Case of a game Equipment
        $gameEquipment = new GameItem();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setHolder($player)
        ;
        $I->haveInRepository($gameEquipment);

        $gameEquipment2 = new GameItem();
        $gameEquipment2
            ->setEquipment($equipmentConfig2)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment2);

        $equipmentEvent = new EquipmentEvent(
            $gameEquipment,
            $room,
            VisibilityEnum::PUBLIC,
            ActionEnum::COFFEE,
            new \DateTime()
        );
        $equipmentEvent->setPlayer($player)->setReplacementEquipment($gameEquipment2);

        $this->eventDispatcherService->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_TRANSFORM);

        $I->assertEquals($room->getEquipments()->count(), 0);
        $I->assertEquals($player->getEquipments()->count(), 1);
        $I->assertEquals($player->getModifiers()->count(), 0);
        $I->assertEquals($room->getModifiers()->count(), 0);
        $I->assertEquals($daedalus->getModifiers()->count(), 1);
        $I->assertEquals($daedalus->getModifiers()->first()->getModifierConfig(), $modifierConfig2);
    }
}
