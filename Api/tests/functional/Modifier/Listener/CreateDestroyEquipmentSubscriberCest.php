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
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;

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
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $I->haveInRepository($modifierConfig);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig]));
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(ItemConfig::class, ['gameConfig' => $gameConfig, 'mechanics' => new ArrayCollection([$gear])]);

        // Case of a game Equipment
        $equipmentEvent = new EquipmentEvent(
            $equipmentConfig->getName(),
            $player,
            VisibilityEnum::PUBLIC,
            ActionEnum::COFFEE,
            new \DateTime()
        );
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
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $I->haveInRepository($modifierConfig);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig]));
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(ItemConfig::class, ['gameConfig' => $gameConfig, 'mechanics' => new ArrayCollection([$gear])]);

        $equipmentEvent = new EquipmentEvent(
            $equipmentConfig->getName(),
            $player,
            VisibilityEnum::PUBLIC,
            ActionEnum::COFFEE,
            new \DateTime()
        );
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
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLACE)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $I->haveInRepository($modifierConfig);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig]));
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(ItemConfig::class, ['gameConfig' => $gameConfig, 'mechanics' => new ArrayCollection([$gear])]);

        $equipmentEvent = new EquipmentEvent(
            $equipmentConfig->getName(),
            $player,
            VisibilityEnum::PUBLIC,
            ActionEnum::COFFEE,
            new \DateTime()
        );
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
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
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

        // Case of a game Equipment
        $gameEquipment = new GameItem();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setHolder($player)
        ;
        $I->haveInRepository($gameEquipment);

        $equipmentEvent = new EquipmentEvent(
            $gameEquipment->getName(),
            $player,
            VisibilityEnum::PUBLIC,
            ActionEnum::COFFEE,
            new \DateTime()
        );
        $equipmentEvent->setExistingEquipment($gameEquipment);
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
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
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

        // Case of a game Equipment
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
            $gameEquipment->getName(),
            $player,
            VisibilityEnum::PUBLIC,
            ActionEnum::COFFEE,
            new \DateTime()
        );
        $equipmentEvent->setExistingEquipment($gameEquipment);
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
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $I->haveInRepository($modifierConfig);

        $modifierConfig2 = new ModifierConfig();
        $modifierConfig2
            ->setScope(ActionEnum::SHOWER)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setMode(ModifierModeEnum::ADDITIVE)
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

        /** @var ItemConfig $equipmentConfig */
        $equipmentConfig = $I->have(ItemConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
        ]);
        /** @var ItemConfig $equipmentConfig */
        $equipmentConfig2 = $I->have(ItemConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear2]),
            'name' => ItemEnum::APPRENTON,
        ]);

        // Case of a game Equipment
        $gameEquipment = new GameItem();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setHolder($player)
        ;
        $I->haveInRepository($gameEquipment);

        $equipmentEvent = new EquipmentEvent(
            $equipmentConfig2->getName(),
            $player,
            VisibilityEnum::PUBLIC,
            ActionEnum::COFFEE,
            new \DateTime()
        );
        $equipmentEvent->setExistingEquipment($gameEquipment);
        $this->eventDispatcherService->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_TRANSFORM);

        $I->assertCount(0, $room->getEquipments());
        $I->assertCount(1, $player->getEquipments());
        $I->assertCount(0, $player->getModifiers());
        $I->assertCount(0, $room->getModifiers());
        $I->assertCount(1, $daedalus->getModifiers());
        $I->assertEquals($daedalus->getModifiers()->first()->getModifierConfig(), $modifierConfig2);
    }
}
