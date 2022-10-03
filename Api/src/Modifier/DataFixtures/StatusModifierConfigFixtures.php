<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Event\PercentageRollEvent;
use Mush\Action\Event\PreparePercentageRollEvent;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Modifier\Entity\Condition\MinimumPlayerInPlaceModifierCondition;
use Mush\Modifier\Entity\Condition\ModifierCondition;
use Mush\Modifier\Entity\Config\Quantity\CostModifierConfig;
use Mush\Modifier\Entity\Config\Quantity\PlayerVariableModifierConfig;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Enum\ModifierConditionEnum;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
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

        $frozenModifier = new ModifierConfig(
            ModifierNameEnum::FROZEN_MODIFIER,
            ModifierReachEnum::EQUIPMENT,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $frozenModifier
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CHANGE_ACTION_POINT, [ActionEnum::CONSUME]);
        $manager->persist($frozenModifier);

        $disabledConversionModifier = new ModifierConfig(
            ModifierNameEnum::DISABLED_CONVERSION_MODIFIER,
            ModifierReachEnum::PLAYER,
            -2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $disabledConversionModifier
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CONVERSION_ACTION_TO_MOVEMENT_POINT_COST);
        $manager->persist($disabledConversionModifier);

        $disabledNotAloneModifier = new ModifierConfig(
            ModifierNameEnum::DISABLED_MOVE_MODIFIER,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $disabledNotAloneModifier
            ->addCondition($notAloneCondition)
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CHANGE_MOVEMENT_POINT, [ActionEnum::MOVE]);
        $manager->persist($disabledNotAloneModifier);

        $pacifistModifier = new ModifierConfig(
            ModifierNameEnum::PACIFIST_MODIFIER,
            ModifierReachEnum::PLACE,
            2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        foreach (ActionTypeEnum::getAgressiveActions() as $action) {
            $pacifistModifier
                ->addTargetEvent(ResourcePointChangeEvent::CHECK_CHANGE_ACTION_POINT, [$action]);
        }
        $manager->persist($pacifistModifier);

        $burdenedModifier = new ModifierConfig(
            ModifierNameEnum::BURDENED_MODIFIER,
            ModifierReachEnum::PLAYER,
            2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        foreach (ActionTypeEnum::getAgressiveActions() as $action) {
            $burdenedModifier
                ->addTargetEvent(ResourcePointChangeEvent::CHECK_CHANGE_MOVEMENT_POINT, [$action]);
        }
        $manager->persist($burdenedModifier);

        $antisocialModifier = new ModifierConfig(
            ModifierNameEnum::ANTISOCIAL_MODIFIER,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MORAL_POINT
        );
        $antisocialModifier
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE])
            ->addCondition($notAloneCondition);
        $manager->persist($antisocialModifier);

        $lostModifier = new ModifierConfig(
            ModifierNameEnum::LOST_MODIFIER,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MORAL_POINT
        );
        $lostModifier
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE]);
        $manager->persist($lostModifier);

        $lyingDownModifier = new ModifierConfig(
            ModifierNameEnum::LYING_DOWN_MODIFIER,
            ModifierReachEnum::PLAYER,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $lyingDownModifier
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE]);
        $manager->persist($lyingDownModifier);

        $starvingModifier = new ModifierConfig(
            ModifierNameEnum::STARVING_MODIFIER,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::HEALTH_POINT
        );
        $starvingModifier
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE])
            ->setLogKeyWhenApplied(ModifierNameEnum::STARVING);
        $manager->persist($starvingModifier);

        $increaseCycleDiseaseChances30 = new ModifierConfig(
            ModifierNameEnum::STATUS_GAIN_30_CYCLE_DISEASE_CHANCE,
            ModifierReachEnum::PLAYER,
            30,
            ModifierModeEnum::ADDITIVE
        );
        $increaseCycleDiseaseChances30
            ->addTargetEvent(PreparePercentageRollEvent::ACTION_ROLL_RATE, [PlayerEvent::CYCLE_DISEASE]);
        $manager->persist($increaseCycleDiseaseChances30);

        $mushShowerModifier = new ModifierConfig(
            ModifierNameEnum::STATUS_MUSH_SHOWER_MODIFIER,
            ModifierReachEnum::PLAYER,
            -3,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::HEALTH_POINT
        );
        $mushShowerModifier
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [ActionEnum::SHOWER])
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [ActionEnum::WASH_IN_SINK])
            ->setLogKeyWhenApplied(ModifierNameEnum::MUSH_SHOWER_MALUS);
        $manager->persist($mushShowerModifier);

        $mushConsumeSatietyModifier = new ModifierConfig(
            ModifierNameEnum::STATUS_MUSH_CONSUME_SATIETY_MODIFIER,
            ModifierReachEnum::PLAYER,
            4,
            ModifierModeEnum::SET_VALUE,
            PlayerVariableEnum::SATIETY
        );
        $mushConsumeSatietyModifier
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [ActionEnum::CONSUME])
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [ActionEnum::CONSUME_DRUG]);
        $manager->persist($mushConsumeSatietyModifier);

        $mushConsumeHealthModifier = new ModifierConfig(
            ModifierNameEnum::STATUS_MUSH_CONSUME_HP_MODIFIER,
            ModifierReachEnum::PLAYER,
            0,
            ModifierModeEnum::SET_VALUE,
            PlayerVariableEnum::HEALTH_POINT
        );
        $mushConsumeHealthModifier
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [ActionEnum::CONSUME])
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [ActionEnum::CONSUME_DRUG]);
        $manager->persist($mushConsumeHealthModifier);

        $mushConsumeMoralModifier = new ModifierConfig(
            ModifierNameEnum::STATUS_MUSH_CONSUME_PMO_MODIFIER,
            ModifierReachEnum::PLAYER,
            0,
            ModifierModeEnum::SET_VALUE,
            PlayerVariableEnum::MORAL_POINT
        );
        $mushConsumeMoralModifier
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [ActionEnum::CONSUME])
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [ActionEnum::CONSUME_DRUG]);
        $manager->persist($mushConsumeMoralModifier);

        $mushConsumeActionModifier = new ModifierConfig(
            ModifierNameEnum::STATUS_MUSH_CONSUME_PA_MODIFIER,
            ModifierReachEnum::PLAYER,
            0,
            ModifierModeEnum::SET_VALUE,
            PlayerVariableEnum::ACTION_POINT
        );
        $mushConsumeActionModifier
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [ActionEnum::CONSUME])
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [ActionEnum::CONSUME_DRUG]);
        $manager->persist($mushConsumeActionModifier);

        $mushConsumeMovementModifier = new ModifierConfig(
            ModifierNameEnum::STATUS_MUSH_CONSUME_PM_MODIFIER,
            ModifierReachEnum::PLAYER,
            0,
            ModifierModeEnum::SET_VALUE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $mushConsumeMovementModifier
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [ActionEnum::CONSUME])
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [ActionEnum::CONSUME_DRUG]);
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
