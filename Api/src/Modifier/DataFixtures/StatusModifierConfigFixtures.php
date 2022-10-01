<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Modifier\Entity\Condition\MinimumPlayerInPlaceModifierCondition;
use Mush\Modifier\Entity\Condition\ModifierCondition;
use Mush\Modifier\Entity\Config\Quantity\CostModifierConfig;
use Mush\Modifier\Entity\Config\Quantity\PlayerVariableModifierConfig;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierConditionEnum;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\ResourcePointChangeEvent;

class StatusModifierConfigFixtures extends Fixture
{
    public const FROZEN_MODIFIER = 'frozen_modifier';
    public const DISABLED_CONVERSION_MODIFIER = 'disabled_conversion_modifier';
    public const DISABLED_NOT_ALONE_MODIFIER = 'disabled_not_alone_modifier';
    public const PACIFIST_MODIFIER = 'pacifist_modifier';
    public const BURDENED_MODIFIER = 'burdened_modifier';
    public const ANTISOCIAL_MODIFIER = 'antisocial_modifier';
    public const LOST_MODIFIER = 'lost_modifier';
    public const LYING_DOWN_MODIFIER = 'lying_down_modifier';
    public const STARVING_MODIFIER = 'starving_modifier';
    public const INCREASE_CYCLE_DISEASE_CHANCES_30 = 'increase_cycle_disease_chances_30';

    public const MUSH_SHOWER_MODIFIER = 'mush_shower_modifier';
    public const MUSH_CONSUME_SATIETY_MODIFIER = 'mush_consume_satiety_modifier';
    public const MUSH_CONSUME_MORAL_MODIFIER = 'mush_consume_moral_modifier';
    public const MUSH_CONSUME_HEALTH_MODIFIER = 'mush_consume_health_modifier';
    public const MUSH_CONSUME_ACTION_MODIFIER = 'mush_consume_action_modifier';
    public const MUSH_CONSUME_MOVEMENT_MODIFIER = 'mush_consume_movement_modifier';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $notAloneCondition = new MinimumPlayerInPlaceModifierCondition(1);
        $manager->persist($notAloneCondition);

        $frozenModifier = new CostModifierConfig(
            ModifierNameEnum::FROZEN_MODIFIER,
            ModifierReachEnum::EQUIPMENT,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $frozenModifier
            ->addTargetEvent(ActionEnum::CONSUME);
        $manager->persist($frozenModifier);

        $disabledConversionModifier = new CostModifierConfig(
            ModifierNameEnum::DISABLED_CONVERSION_MODIFIER,
            ModifierReachEnum::PLAYER,
            -2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $disabledConversionModifier
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CONVERSION_ACTION_TO_MOVEMENT_POINT_COST);
        $manager->persist($disabledConversionModifier);

        $disabledNotAloneModifier = new CostModifierConfig(
            ModifierNameEnum::DISABLED_MOVE_MODIFIER,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $disabledNotAloneModifier
            ->addCondition($notAloneCondition)
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CHANGE_MOVEMENT_POINT);
        $manager->persist($disabledNotAloneModifier);

        $pacifistModifier = new CostModifierConfig(
            ModifierNameEnum::PACIFIST_MODIFIER,
            ModifierReachEnum::PLACE,
            2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $pacifistModifier
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CHANGE_ACTION_POINT, ActionTypeEnum::getAgressiveActions());
        $manager->persist($pacifistModifier);

        $burdenedModifier = new CostModifierConfig(
            ModifierNameEnum::BURDENED_MODIFIER,
            ModifierReachEnum::PLAYER,
            2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $burdenedModifier
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CHANGE_MOVEMENT_POINT, ActionTypeEnum::getAgressiveActions());
        $manager->persist($burdenedModifier);

        $antisocialModifier = new PlayerVariableModifierConfig(
            ModifierNameEnum::ANTISOCIAL_MODIFIER,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MORAL_POINT
        );
        $manager->persist($antisocialModifier);

        $lostModifier = new CostModifierConfig(

        );
        $lostModifier
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $lostModifier
            ->addTargetEvent();
        $manager->persist($lostModifier);

        $lyingDownModifier = new ModifierConfig();
        $lyingDownModifier
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setName(ModifierNameEnum::LYING_DOWN_MODIFIER)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $manager->persist($lyingDownModifier);

        $starvingModifier = new ModifierConfig();
        $starvingModifier
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setName(ModifierNameEnum::STARVING)
        ;
        $manager->persist($starvingModifier);

        $increaseCycleDiseaseChances30 = new ModifierConfig();
        $increaseCycleDiseaseChances30
            ->setScope(PlayerEvent::CYCLE_DISEASE)
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(30)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $manager->persist($increaseCycleDiseaseChances30);

        $showerActionCondition = new ModifierCondition(ModifierConditionEnum::REASON);
        $showerActionCondition->setCondition(ActionEnum::SHOWER);
        $manager->persist($showerActionCondition);

        $mushShowerModifier = new ModifierConfig();
        $mushShowerModifier
            ->setScope(ActionEvent::POST_ACTION)
            ->setTarget(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-3)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($showerActionCondition)
            ->setName(ModifierNameEnum::MUSH_SHOWER_MALUS)
        ;
        $manager->persist($mushShowerModifier);

        $sinkActionCondition = new ModifierCondition(ModifierConditionEnum::REASON);
        $sinkActionCondition->setCondition(ActionEnum::WASH_IN_SINK);
        $manager->persist($sinkActionCondition);

        $mushSinkModifier = new ModifierConfig();
        $mushSinkModifier
            ->setScope(ActionEvent::POST_ACTION)
            ->setTarget(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-3)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($sinkActionCondition)
            ->setName(ModifierNameEnum::MUSH_SHOWER_MALUS)
        ;
        $manager->persist($mushSinkModifier);

        $consumeActionCondition = new ModifierCondition(ModifierConditionEnum::REASON);
        $consumeActionCondition->setCondition(ActionEnum::CONSUME);
        $manager->persist($consumeActionCondition);

        $mushConsumeSatietyModifier = new ModifierConfig();
        $mushConsumeSatietyModifier
            ->setScope(AbstractQuantityEvent::CHANGE_VARIABLE)
            ->setTarget(PlayerVariableEnum::SATIETY)
            ->setDelta(4)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($consumeActionCondition)
        ;
        $manager->persist($mushConsumeSatietyModifier);

        $mushConsumeHealthModifier = new ModifierConfig();
        $mushConsumeHealthModifier
            ->setScope(AbstractQuantityEvent::CHANGE_VARIABLE)
            ->setTarget(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(0)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($consumeActionCondition)
        ;
        $manager->persist($mushConsumeHealthModifier);

        $mushConsumeMoralModifier = new ModifierConfig();
        $mushConsumeMoralModifier
            ->setScope(AbstractQuantityEvent::CHANGE_VARIABLE)
            ->setTarget(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(0)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($consumeActionCondition)
        ;
        $manager->persist($mushConsumeMoralModifier);

        $mushConsumeActionModifier = new ModifierConfig();
        $mushConsumeActionModifier
            ->setScope(AbstractQuantityEvent::CHANGE_VARIABLE)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(0)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($consumeActionCondition)
        ;
        $manager->persist($mushConsumeActionModifier);

        $mushConsumeMovementModifier = new ModifierConfig();
        $mushConsumeMovementModifier
            ->setScope(AbstractQuantityEvent::CHANGE_VARIABLE)
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(0)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($consumeActionCondition)
        ;
        $manager->persist($mushConsumeMovementModifier);

        $manager->flush();

        $this->addReference(self::FROZEN_MODIFIER, $frozenModifier);
        $this->addReference(self::DISABLED_CONVERSION_MODIFIER, $disabledConversionModifier);
        $this->addReference(self::DISABLED_NOT_ALONE_MODIFIER, $disabledNotAloneModifier);
        $this->addReference(self::PACIFIST_MODIFIER, $pacifistModifier);
        $this->addReference(self::BURDENED_MODIFIER, $burdenedModifier);
        $this->addReference(self::ANTISOCIAL_MODIFIER, $antisocialModifier);
        $this->addReference(self::LOST_MODIFIER, $lostModifier);
        $this->addReference(self::LYING_DOWN_MODIFIER, $lyingDownModifier);
        $this->addReference(self::STARVING_MODIFIER, $starvingModifier);
        $this->addReference(self::INCREASE_CYCLE_DISEASE_CHANCES_30, $increaseCycleDiseaseChances30);

        $this->addReference(self::MUSH_SHOWER_MODIFIER, $mushShowerModifier);
        $this->addReference(self::MUSH_CONSUME_ACTION_MODIFIER, $mushConsumeActionModifier);
        $this->addReference(self::MUSH_CONSUME_MOVEMENT_MODIFIER, $mushConsumeMovementModifier);
        $this->addReference(self::MUSH_CONSUME_HEALTH_MODIFIER, $mushConsumeHealthModifier);
        $this->addReference(self::MUSH_CONSUME_MORAL_MODIFIER, $mushConsumeMoralModifier);
        $this->addReference(self::MUSH_CONSUME_SATIETY_MODIFIER, $mushConsumeSatietyModifier);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
