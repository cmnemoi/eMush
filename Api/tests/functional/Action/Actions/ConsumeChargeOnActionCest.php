<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Coffee;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class ConsumeChargeOnActionCest
{
    private Coffee $coffeeAction;

    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        $this->coffeeAction = $I->grabService(Coffee::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testToolCharge(FunctionalTester $I)
    {
        $attemptConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['statusName' => StatusEnum::ATTEMPT]);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setDischargeStrategies([ActionEnum::COFFEE->value])
            ->buildName(GameConfigEnum::TEST)
            ->setStartCharge(2);
        $I->haveInRepository($statusConfig);

        $actionEntity = new ActionConfig();
        $actionEntity
            ->setActionName(ActionEnum::COFFEE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($actionEntity);

        $tool = new Tool();
        $tool->addAction($actionEntity)->buildName(ItemEnum::FUEL_CAPSULE, GameConfigEnum::TEST);
        $I->haveInRepository($tool);

        $equipment = new ItemConfig();
        $equipment
            ->setEquipmentName(ItemEnum::FUEL_CAPSULE)
            ->setMechanics(new ArrayCollection([$tool]))
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($equipment);
        $equipmentCoffee = new ItemConfig();
        $equipmentCoffee
            ->setEquipmentName(GameRationEnum::COFFEE)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($equipmentCoffee);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$attemptConfig, $statusConfig]))
            ->setEquipmentsConfig(new ArrayCollection([$equipment, $equipmentCoffee]));
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
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
            ->setActionPoint(10)
            ->setHealthPoint(10);
        $I->flushToDatabase($player);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->haveInRepository($player);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipment)
            ->setName(ItemEnum::FUEL_CAPSULE);
        $I->haveInRepository($gameEquipment);

        /** @var ChargeStatus $chargeStatus */
        $chargeStatus = $this->statusService->createStatusFromConfig(
            $statusConfig,
            $gameEquipment,
            [],
            new \DateTime()
        );

        $this->coffeeAction->loadParameters(
            actionConfig: $actionEntity,
            actionProvider: $gameEquipment,
            player: $player,
            target: $gameEquipment
        );

        $I->assertEquals(0, $this->coffeeAction->getMovementPointCost());
        $I->assertEquals(2, $this->coffeeAction->getActionPointCost());
        $I->assertEquals(2, $chargeStatus->getCharge());

        $this->coffeeAction->execute();

        $I->assertEquals(1, $chargeStatus->getCharge());
    }

    public function testGearCharge(FunctionalTester $I)
    {
        $equipmentCoffee = new ItemConfig();
        $equipmentCoffee
            ->setEquipmentName(GameRationEnum::COFFEE)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($equipmentCoffee);

        $attemptConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['statusName' => StatusEnum::ATTEMPT]);

        $actionEntity = new ActionConfig();
        $actionEntity
            ->setActionName(ActionEnum::COFFEE)
            ->setRange(ActionRangeEnum::SELF)
            ->setActionCost(2)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($actionEntity);

        $equipment = new ItemConfig();
        $equipment
            ->setEquipmentName(ItemEnum::FUEL_CAPSULE)
            ->setActionConfigs(new ArrayCollection([$actionEntity]))
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($equipment);
        $modifierConfig = new VariableEventModifierConfig('modifierForTestConsumeChargeOnAction');
        $modifierConfig
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setModifierRange(ReachEnum::INVENTORY)
            ->setMode(VariableModifierModeEnum::ADDITIVE);
        $gearMechanic = new Gear();
        $gearMechanic
            ->setModifierConfigs(new ArrayCollection([$modifierConfig]))
            ->buildName(EquipmentMechanicEnum::GEAR, GameConfigEnum::TEST);
        $gearConfig = new ItemConfig();
        $gearConfig
            ->setEquipmentName(GearItemEnum::SOAP)
            ->setMechanics(new ArrayCollection([$gearMechanic]))
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($modifierConfig);
        $I->haveInRepository($gearMechanic);
        $I->haveInRepository($gearConfig);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$attemptConfig]))
            ->setEquipmentsConfig(new ArrayCollection([$equipment, $equipmentCoffee, $gearConfig]));
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
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
            ->setActionPoint(10)
            ->setHealthPoint(10);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->haveInRepository($player);

        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipment)
            ->setName(ItemEnum::FUEL_CAPSULE);
        $I->haveInRepository($gameEquipment);

        $I->haveInRepository($room);

        $gameGear = new GameItem($player);
        $gameGear
            ->setName(GearItemEnum::SOAP)
            ->setEquipment($gearConfig);
        $I->haveInRepository($gameGear);

        $I->haveInRepository($player);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setDischargeStrategies([ActionEnum::COFFEE->value])
            ->buildName(GameConfigEnum::TEST)
            ->setStartCharge(1);
        $I->haveInRepository($statusConfig);

        /** @var ChargeStatus $chargeStatus */
        $chargeStatus = $this->statusService->createStatusFromConfig(
            $statusConfig,
            $gameEquipment,
            [],
            new \DateTime()
        );

        $modifier = new GameModifier($player, $modifierConfig);
        $modifier->setCharge($chargeStatus);
        $I->haveInRepository($modifier);

        $this->coffeeAction->loadParameters(
            actionConfig: $actionEntity,
            actionProvider: $gameEquipment,
            player: $player,
            target: $gameEquipment
        );

        $I->assertEquals(0, $this->coffeeAction->getMovementPointCost());
        $I->assertEquals(1, $this->coffeeAction->getActionPointCost());
        $I->assertEquals(1, $chargeStatus->getCharge());

        $this->coffeeAction->execute();

        $I->assertEquals(0, $chargeStatus->getCharge());
        $I->assertEquals(0, $this->coffeeAction->getMovementPointCost());
        $I->assertEquals(2, $this->coffeeAction->getActionPointCost());
    }

    public function testGearMovementActionConversionCharge(FunctionalTester $I)
    {
        $actionEntity = new ActionConfig();
        $actionEntity
            ->setActionName(ActionEnum::COFFEE)
            ->setRange(ActionRangeEnum::SELF)
            ->setMovementCost(1)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($actionEntity);

        $convertActionEntity = new ActionConfig();
        $convertActionEntity
            ->setActionName(ActionEnum::CONVERT_ACTION_TO_MOVEMENT)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->buildName(GameConfigEnum::TEST);
        $convertActionEntity->getGameVariables()->setValuesByName(['value' => 1, 'min_value' => 0, 'max_value' => null], PlayerVariableEnum::ACTION_POINT);
        $convertActionEntity->getGameVariables()->setValuesByName(['value' => -2, 'min_value' => null, 'max_value' => 0], PlayerVariableEnum::MOVEMENT_POINT);
        $I->haveInRepository($convertActionEntity);

        $equipmentCoffee = new ItemConfig();
        $equipmentCoffee
            ->setEquipmentName(GameRationEnum::COFFEE)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($equipmentCoffee);
        $equipment = new ItemConfig();
        $equipment
            ->setEquipmentName(ItemEnum::FUEL_CAPSULE)
            ->setActionConfigs(new ArrayCollection([$actionEntity]))
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($equipment);
        $modifierConfig = new VariableEventModifierConfig('modifierForTestConsumeChargeOnAction');
        $modifierConfig
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-1)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([ActionEnum::CONVERT_ACTION_TO_MOVEMENT->value => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ReachEnum::INVENTORY)
            ->setMode(VariableModifierModeEnum::ADDITIVE);

        $gearMechanic = new Gear();
        $gearMechanic
            ->setModifierConfigs(new ArrayCollection([$modifierConfig]))
            ->setName(EquipmentMechanicEnum::GEAR);
        $gearConfig = new ItemConfig();
        $gearConfig
            ->setEquipmentName(GearItemEnum::SOAP)
            ->setMechanics(new ArrayCollection([$gearMechanic]))
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($modifierConfig);
        $I->haveInRepository($gearMechanic);
        $I->haveInRepository($gearConfig);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setDischargeStrategies([ActionEnum::COFFEE])
            ->buildName(GameConfigEnum::TEST)
            ->setStartCharge(1);
        $I->haveInRepository($statusConfig);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$statusConfig]))
            ->setEquipmentsConfig(new ArrayCollection([$equipment, $equipmentCoffee, $gearConfig]));
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
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
            ->setActionPoint(10)
            ->setHealthPoint(10)
            ->setMovementPoint(0);
        $I->flushToDatabase($player);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->haveInRepository($player);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipment)
            ->setName(ItemEnum::FUEL_CAPSULE);

        $I->haveInRepository($gameEquipment);

        $I->haveInRepository($room);

        $gameGear = new GameItem($player);
        $gameGear
            ->setName(GearItemEnum::SOAP)
            ->setEquipment($gearConfig);
        $I->haveInRepository($gameGear);

        $I->haveInRepository($player);

        /** @var ChargeStatus $chargeStatus */
        $chargeStatus = $this->statusService->createStatusFromConfig(
            $statusConfig,
            $gameEquipment,
            [],
            new \DateTime()
        );

        $modifier = new GameModifier($player, $modifierConfig);
        $modifier->setCharge($chargeStatus);
        $I->haveInRepository($modifier);

        $this->coffeeAction->loadParameters(
            actionConfig: $actionEntity,
            actionProvider: $gameEquipment,
            player: $player,
            target: $gameEquipment
        );

        $I->assertEquals(1, $this->coffeeAction->getMovementPointCost());
        $I->assertEquals(0, $this->coffeeAction->getActionPointCost());
        $I->assertEquals(1, $chargeStatus->getCharge());

        $this->coffeeAction->execute();

        $I->assertEquals(0, $chargeStatus->getCharge());
        $I->assertEquals(1, $this->coffeeAction->getMovementPointCost());
        $I->assertEquals(0, $this->coffeeAction->getActionPointCost());
    }
}
