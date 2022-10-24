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
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreateDestroyEquipmentSubscriberCest
{
    private EventDispatcherInterface $eventDispatcher;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I): void
    {
        $this->eventDispatcher = $I->grabService(EventDispatcherInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function testCreateGearPlayerScope(FunctionalTester $I): void
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

        $name = 'test item';

        /* @var EquipmentConfig $equipmentConfig */
        $I->have(ItemConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
            'name' => $name,
        ]);

        $this->gameEquipmentService->createGameEquipmentFromName(
            $name,
            $player,
            'a test reason',
            VisibilityEnum::PUBLIC
        );

        $I->assertEquals($room->getEquipments()->count(), 0);
        $I->assertEquals($player->getEquipments()->count(), 1);
        $I->assertEquals($player->getModifiers()->count(), 1);
        $I->assertEquals($room->getModifiers()->count(), 0);
        $I->assertEquals($player->getModifiers()->first()->getModifierConfig(), $modifierConfig);
    }

    public function testCreateGearPlayerScopeInventoryFull(FunctionalTester $I): void
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

        $name = 'test item';

        $I->have(ItemConfig::class, [
            'name' => $name,
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
        ]);

        $this->gameEquipmentService->createGameEquipmentFromName(
            $name,
            $player,
            'a test reason',
            VisibilityEnum::PUBLIC
        );

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

        $name = 'test name';
        $I->have(ItemConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
            'name' => $name,
        ]);

        $this->gameEquipmentService->createGameEquipmentFromName(
            $name,
            $player,
            'a test reason',
            VisibilityEnum::PUBLIC
        );

        $I->assertEquals($room->getEquipments()->count(), 0);
        $I->assertEquals($player->getEquipments()->count(), 1);
        $I->assertEquals($player->getModifiers()->count(), 0);
        $I->assertEquals($room->getModifiers()->count(), 1);
        $I->assertEquals($room->getModifiers()->first()->getModifierConfig(), $modifierConfig);
    }

    public function testDestroyGear(FunctionalTester $I): void
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

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig]));
        $I->haveInRepository($gear);

        $name = 'test name';

        /* @var EquipmentConfig $equipmentConfig */
        $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
            'name' => $name,
        ]);

        $equipment = $this->gameEquipmentService->createGameEquipmentFromName(
            $name,
            $player,
            'a test reason',
            VisibilityEnum::PUBLIC
        );

        $destroyEvent = new EquipmentEvent(
            $equipment,
            false,
            VisibilityEnum::PUBLIC,
            ActionEnum::COFFEE,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($destroyEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $I->assertEquals($room->getEquipments()->count(), 0);
        $I->assertEquals($player->getEquipments()->count(), 0);
        $I->assertEquals($player->getModifiers()->count(), 0);
        $I->assertEquals($room->getModifiers()->count(), 0);
    }

    public function testDestroyOneOfTwoGear(FunctionalTester $I): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'maxItemInInventory' => 2,
        ]);

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
            'characterConfig' => $characterConfig,
        ]);

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

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig]));
        $I->haveInRepository($gear);

        $name = 'test name';

        $I->have(ItemConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
            'name' => $name,
        ]);

        // Case of a game Equipment
        $gameEquipment = $this->gameEquipmentService->createGameEquipmentFromName(
            $name,
            $player,
            'a test reason',
            VisibilityEnum::PUBLIC
        );

        $gameEquipment2 = $this->gameEquipmentService->createGameEquipmentFromName(
            $name,
            $player,
            'a test reason',
            VisibilityEnum::PUBLIC
        );

        $I->assertEquals(2, $player->getEquipments()->count());

        $destroyEvent = new EquipmentEvent(
            $gameEquipment,
            false,
            VisibilityEnum::PUBLIC,
            ActionEnum::COFFEE,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($destroyEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $I->assertEquals(0, $room->getEquipments()->count());
        $I->assertEquals(1, $player->getEquipments()->count());
        $I->assertEquals(1, $player->getModifiers()->count());
        $I->assertEquals(0, $room->getModifiers()->count());
    }

    public function testTransformGear(FunctionalTester $I): void
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
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'characterConfig' => $characterConfig,
        ]);

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
            'name' => ItemEnum::OXYGEN_CAPSULE,
        ]);
        /** @var ItemConfig $equipmentConfig2 */
        $equipmentConfig2 = $I->have(ItemConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear2]),
            'name' => ItemEnum::APPRENTON,
        ]);

        // Case of a game Equipment
        $gameEquipment = $this->gameEquipmentService->createGameEquipmentFromName(
            ItemEnum::OXYGEN_CAPSULE,
            $player,
            'a test reason',
            VisibilityEnum::PUBLIC
        );

        $transformedEquipment = $this->gameEquipmentService->transformGameEquipmentToEquipmentWithName(
            ItemEnum::APPRENTON,
            $gameEquipment,
            $player,
            'a test reason',
            VisibilityEnum::PUBLIC
        );

        $I->assertCount(0, $room->getEquipments());
        $I->assertCount(1, $player->getEquipments());
        $I->assertCount(0, $player->getModifiers());
        $I->assertCount(0, $room->getModifiers());
        $I->assertCount(1, $daedalus->getModifiers());
        $I->assertEquals($daedalus->getModifiers()->first()->getModifierConfig(), $modifierConfig2);
    }
}
