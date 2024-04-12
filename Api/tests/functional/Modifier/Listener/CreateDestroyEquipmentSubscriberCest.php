<?php

namespace Mush\Tests\functional\Modifier\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class CreateDestroyEquipmentSubscriberCest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I): void
    {
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function testCreateGearPlayerScope(FunctionalTester $I): void
    {
        $modifierConfig = $I->grabEntityFromRepository(VariableEventModifierConfig::class, [
            'name' => 'soapShowerActionModifier',
        ]);

        $gear = new Gear();
        $gear
            ->setModifierConfigs(new ArrayCollection([$modifierConfig]))
            ->setName('gear_test');
        $I->haveInRepository($gear);

        $name = 'test item';

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(ItemConfig::class, [
            'mechanics' => new ArrayCollection([$gear]),
            'name' => $name,
            'equipmentName' => $name,
        ]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'equipmentsConfig' => new ArrayCollection([$equipmentConfig]),
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['maxItemInInventory' => 1]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $this->gameEquipmentService->createGameEquipmentFromName(
            $name,
            $player,
            ['a test reason'],
            new \DateTime(),
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
        $modifierConfig = $I->grabEntityFromRepository(VariableEventModifierConfig::class, [
            'name' => 'soapShowerActionModifier',
        ]);

        $gear = new Gear();
        $gear
            ->setModifierConfigs(new ArrayCollection([$modifierConfig]))
            ->setName('gear_test');
        $I->haveInRepository($gear);

        $name = 'test item';

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, [
            'name' => $name,
            'equipmentName' => $name,
            'mechanics' => new ArrayCollection([$gear]),
        ]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['equipmentsConfig' => new ArrayCollection([$itemConfig])]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['maxItemInInventory' => 0]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $this->gameEquipmentService->createGameEquipmentFromName(
            $name,
            $player,
            ['a test reason'],
            new \DateTime(),
            VisibilityEnum::PUBLIC
        );

        $I->assertEquals($room->getEquipments()->count(), 1);
        $I->assertEquals($player->getEquipments()->count(), 0);
        $I->assertEquals($player->getModifiers()->count(), 0);
        $I->assertEquals($room->getModifiers()->count(), 0);
    }

    public function testCreateGearPlaceReach(FunctionalTester $I)
    {
        $modifierConfig = new VariableEventModifierConfig('modifierShowerActionTest');
        $modifierConfig
            ->setTargetEvent(ActionEnum::SHOWER)
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setModifierRange(ModifierHolderClassEnum::PLACE)
            ->setMode(VariableModifierModeEnum::ADDITIVE);
        $I->haveInRepository($modifierConfig);

        $gear = new Gear();
        $gear
            ->setModifierConfigs(new ArrayCollection([$modifierConfig]))
            ->setName('gear_test');
        $I->haveInRepository($gear);

        $name = 'test name';

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, [
            'mechanics' => new ArrayCollection([$gear]),
            'equipmentName' => $name,
            'name' => $name,
        ]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'equipmentsConfig' => new ArrayCollection([$itemConfig]),
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['maxItemInInventory' => 1]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);

        $this->gameEquipmentService->createGameEquipmentFromName(
            $name,
            $player,
            ['a test reason'],
            new \DateTime(),
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
        $takeActionEntity = new Action();
        $takeActionEntity
            ->setActionName(ActionEnum::DROP)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($takeActionEntity);

        $modifierConfig = $I->grabEntityFromRepository(VariableEventModifierConfig::class, [
            'name' => 'soapShowerActionModifier',
        ]);

        $gear = new Gear();
        $gear
            ->setModifierConfigs(new ArrayCollection([$modifierConfig]))
            ->setName('gear_test');
        $I->haveInRepository($gear);

        $name = 'test name';

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$gear]),
            'name' => $name,
            'equipmentName' => $name,
        ]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'equipmentsConfig' => new ArrayCollection([$equipmentConfig]),
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);

        $equipment = $this->gameEquipmentService->createGameEquipmentFromName(
            $name,
            $player,
            ['a test reason'],
            new \DateTime(),
            VisibilityEnum::PUBLIC
        );

        $destroyEvent = new EquipmentEvent(
            $equipment,
            false,
            VisibilityEnum::PUBLIC,
            [ActionEnum::COFFEE],
            new \DateTime()
        );
        $this->eventService->callEvent($destroyEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $I->assertEquals($room->getEquipments()->count(), 0);
        $I->assertEquals($player->getEquipments()->count(), 0);
        $I->assertEquals($player->getModifiers()->count(), 0);
        $I->assertEquals($room->getModifiers()->count(), 0);
    }

    public function testDestroyOneOfTwoGear(FunctionalTester $I): void
    {
        $takeActionEntity = new Action();
        $takeActionEntity
            ->setActionName(ActionEnum::DROP)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($takeActionEntity);

        $modifierConfig = $I->grabEntityFromRepository(VariableEventModifierConfig::class, [
            'name' => 'soapShowerActionModifier',
        ]);

        $gear = new Gear();
        $gear
            ->setModifierConfigs(new ArrayCollection([$modifierConfig]))
            ->setName('gear_test');
        $I->haveInRepository($gear);

        $name = 'test name';

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, [
            'mechanics' => new ArrayCollection([$gear]),
            'name' => $name,
            'equipmentName' => $name,
        ]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'equipmentsConfig' => new ArrayCollection([$itemConfig]),
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
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
            'characterConfig' => $characterConfig,
        ]);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);

        // Case of a game Equipment
        $gameEquipment = $this->gameEquipmentService->createGameEquipmentFromName(
            $name,
            $player,
            ['a test reason'],
            new \DateTime(),
            VisibilityEnum::PUBLIC
        );

        $gameEquipment2 = $this->gameEquipmentService->createGameEquipmentFromName(
            $name,
            $player,
            ['a test reason'],
            new \DateTime(),
            VisibilityEnum::PUBLIC
        );

        $I->assertEquals(2, $player->getEquipments()->count());

        $destroyEvent = new EquipmentEvent(
            $gameEquipment,
            false,
            VisibilityEnum::PUBLIC,
            [ActionEnum::COFFEE],
            new \DateTime()
        );
        $this->eventService->callEvent($destroyEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $I->assertEquals(0, $room->getEquipments()->count());
        $I->assertEquals(1, $player->getEquipments()->count());
        $I->assertEquals(1, $player->getModifiers()->count());
        $I->assertEquals(0, $room->getModifiers()->count());
    }

    public function testTransformGear(FunctionalTester $I): void
    {
        $takeActionEntity = new Action();
        $takeActionEntity
            ->setActionName(ActionEnum::DROP)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($takeActionEntity);

        $modifierConfig = $I->grabEntityFromRepository(VariableEventModifierConfig::class, [
            'name' => 'soapShowerActionModifier',
        ]);

        $modifierConfig2 = new VariableEventModifierConfig('modifierShowerActionTest');
        $modifierConfig2
            ->setTargetEvent(ActionEnum::SHOWER)
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setMode(VariableModifierModeEnum::ADDITIVE);
        $I->haveInRepository($modifierConfig2);

        $gear = new Gear();
        $gear
            ->setModifierConfigs(new ArrayCollection([$modifierConfig]))
            ->setName('gear_test');
        $I->haveInRepository($gear);

        $gear2 = new Gear();
        $gear2
            ->setModifierConfigs(new ArrayCollection([$modifierConfig2]))
            ->setName('gear_test_2');
        $I->haveInRepository($gear2);

        /** @var ItemConfig $equipmentConfig */
        $equipmentConfig = $I->have(ItemConfig::class, [
            'mechanics' => new ArrayCollection([$gear]),
            'name' => ItemEnum::OXYGEN_CAPSULE,
            'equipmentName' => ItemEnum::OXYGEN_CAPSULE,
        ]);

        /** @var ItemConfig $equipmentConfig2 */
        $equipmentConfig2 = $I->have(ItemConfig::class, [
            'mechanics' => new ArrayCollection([$gear2]),
            'name' => ItemEnum::APPRENTON,
            'equipmentName' => ItemEnum::APPRENTON,
        ]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'equipmentsConfig' => new ArrayCollection([$equipmentConfig, $equipmentConfig2]),
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
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

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);

        // Case of a game Equipment
        $gameEquipment = $this->gameEquipmentService->createGameEquipmentFromName(
            ItemEnum::OXYGEN_CAPSULE,
            $player,
            ['a test reason'],
            new \DateTime(),
            VisibilityEnum::PUBLIC
        );

        $transformedEquipment = $this->gameEquipmentService->transformGameEquipmentToEquipmentWithName(
            ItemEnum::APPRENTON,
            $gameEquipment,
            $player,
            ['a test reason'],
            new \DateTime(),
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
