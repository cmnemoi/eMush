<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Actions\Consume;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Event\PreparePercentageRollEvent;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Modifier\Entity\Condition\ModifierCondition;
use Mush\Modifier\Entity\Condition\PlayerHasStatusModifierCondition;
use Mush\Modifier\Entity\Condition\RandomModifierCondition;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierConditionEnum;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Event\ResourceMaxPointEvent;
use Mush\Status\Enum\PlayerStatusEnum;

class DiseaseModifierConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const REDUCE_MAX_1_HEALTH_POINT = 'reduce_max_1_health_point';
    public const REDUCE_MAX_2_HEALTH_POINT = 'reduce_max_2_health_point';
    public const REDUCE_MAX_4_HEALTH_POINT = 'reduce_max_4_health_point';
    public const REDUCE_MAX_1_MORAL_POINT = 'reduce_max_1_moral_point';
    public const REDUCE_MAX_2_MORAL_POINT = 'reduce_max_2_moral_point';
    public const CYCLE_1_HEALTH_LOST = 'cycle_1_health_lost';
    public const CYCLE_2_HEALTH_LOST = 'cycle_2_health_lost';
    public const CYCLE_4_HEALTH_LOST = 'cycle_4_health_lost';
    public const CYCLE_1_MOVEMENT_LOST = 'cycle_1_movement_lost';
    public const CYCLE_1_SATIETY_LOST = 'cycle_1_satiety_lost';
    public const CYCLE_1_SATIETY_INCREASE = 'cycle_1_satiety_increase';
    public const CYCLE_1_ACTION_LOST_RAND_10 = 'cycle_1_action_lost_rand_10';
    public const CYCLE_1_HEALTH_LOST_RAND_10 = 'cycle_1_health_lost_rand_10';
    public const CYCLE_1_ACTION_LOST_RAND_16 = 'cycle_1_action_lost_rand_16';
    public const CYCLE_1_HEALTH_LOST_RAND_16 = 'cycle_1_health_lost_rand_16';
    public const CYCLE_1_ACTION_LOST_RAND_16_FITFULL_SLEEP = 'cycle_1_action_lost_rand_16_fitfull_sleep';
    public const CYCLE_1_ACTION_LOST_RAND_20 = 'cycle_1_action_lost_rand_20';
    public const CYCLE_1_ACTION_LOST_RAND_30 = 'cycle_1_action_lost_rand_30';
    public const CYCLE_2_ACTION_LOST_RAND_40 = 'cycle_2_action_lost_rand_40';
    public const CYCLE_1_MOVEMENT_LOST_RAND_50 = 'cycle_1_movement_lost_rand_50';
    public const CYCLE_1_HEALTH_LOST_RAND_50 = 'cycle_1_health_lost_rand_50';
    public const CONSUME_1_ACTION_LOSS = 'consume_1_action_loss';
    public const CONSUME_2_ACTION_LOSS = 'consume_2_action_loss';
    public const SHOOT_ACTION_10_PERCENT_ACCURACY_LOST = 'shoot_action_10_percent_accuracy_lost';
    public const MOVE_INCREASE_MOVEMENT = 'move_increase_movement';
    public const TAKE_CAT_6_HEALTH_LOSS = 'take_cat_6_health_loss';
    public const INFECTED_4_HEALTH_LOSS = 'infected_4_health_loss';
    public const INCREASE_CYCLE_DISEASE_CHANCES_10 = 'increase_cycle_disease_chances_10';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $randCondition10 = new RandomModifierCondition(10);
        $manager->persist($randCondition10);

        $randCondition16 = new RandomModifierCondition(16);
        $manager->persist($randCondition16);

        $randCondition20 = new RandomModifierCondition(20);
        $manager->persist($randCondition20);

        $randCondition30 = new RandomModifierCondition(30);
        $manager->persist($randCondition30);

        $randCondition40 = new RandomModifierCondition(40);
        $manager->persist($randCondition40);

        $randCondition50 = new RandomModifierCondition(50);
        $manager->persist($randCondition50);

        $lyingDownCondition = new PlayerHasStatusModifierCondition(PlayerStatusEnum::LYING_DOWN);
        $manager->persist($lyingDownCondition);

        $moveIncreaseMovement = new ModifierConfig(
            ModifierNameEnum::DISEASE_INCREASE_MOVE_COST,
            ModifierReachEnum::PLAYER,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $moveIncreaseMovement
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [ActionEnum::MOVE]);
        $manager->persist($moveIncreaseMovement);

        $reduceMax1HealthPoint = new ModifierConfig(
            ModifierNameEnum::DISEASE_REDUCE_1_MAX_HP,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::HEALTH_POINT
        );
        $reduceMax1HealthPoint
            ->addTargetEvent(ResourceMaxPointEvent::CHECK_MAX_POINT);
        $manager->persist($reduceMax1HealthPoint);

        $reduceMax2HealthPoint = new ModifierConfig(
            ModifierNameEnum::DISEASE_REDUCE_2_MAX_HP,
            ModifierReachEnum::PLAYER,
            -2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::HEALTH_POINT
        );
        $reduceMax2HealthPoint
            ->addTargetEvent(ResourceMaxPointEvent::CHECK_MAX_POINT);
        $manager->persist($reduceMax2HealthPoint);

        $reduceMax4HealthPoint = new ModifierConfig(
            ModifierNameEnum::DISEASE_REDUCE_4_MAX_HP,
            ModifierReachEnum::PLAYER,
            -4,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::HEALTH_POINT
        );
        $reduceMax4HealthPoint
            ->addTargetEvent(ResourceMaxPointEvent::CHECK_MAX_POINT);
        $manager->persist($reduceMax4HealthPoint);

        $reduceMax1MoralPoint = new ModifierConfig(
            ModifierNameEnum::DISEASE_REDUCE_1_MAX_PMO,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MORAL_POINT
        );
        $reduceMax1MoralPoint
            ->addTargetEvent(ResourceMaxPointEvent::CHECK_MAX_POINT);
        $manager->persist($reduceMax1MoralPoint);

        $reduceMax2MoralPoint = new ModifierConfig(
            ModifierNameEnum::DISEASE_REDUCE_2_MAX_PMO,
            ModifierReachEnum::PLAYER,
            -2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MORAL_POINT
        );
        $reduceMax2MoralPoint
            ->addTargetEvent(ResourceMaxPointEvent::CHECK_MAX_POINT);
        $manager->persist($reduceMax2MoralPoint);

        $cycle1HealthLost = new ModifierConfig(
            ModifierNameEnum::DISEASE_LOSE_1_HP_PER_CYCLE,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::HEALTH_POINT
        );
        $cycle1HealthLost
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE]);
        $manager->persist($cycle1HealthLost);

        $cycle2HealthLost = new ModifierConfig(
            ModifierNameEnum::DISEASE_LOSE_2_HP_PER_CYCLE,
            ModifierReachEnum::PLAYER,
            -2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::HEALTH_POINT
        );
        $cycle2HealthLost
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE]);
        $manager->persist($cycle2HealthLost);

        $cycle4HealthLost = new ModifierConfig(
            ModifierNameEnum::DISEASE_LOSE_4_HP_PER_CYCLE,
            ModifierReachEnum::PLAYER,
            -4,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::HEALTH_POINT
        );
        $cycle4HealthLost
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE]);
        $manager->persist($cycle4HealthLost);

        $cycle1MovementLost = new ModifierConfig(
            ModifierNameEnum::DISEASE_LOSE_1_PM_PER_CYCLE,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $cycle1MovementLost
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE]);
        $manager->persist($cycle1MovementLost);

        $cycle1SatietyLost = new ModifierConfig(
            ModifierNameEnum::DISEASE_LOSE_1_SATIETY_PER_CYCLE,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::SATIETY
        );
        $cycle1SatietyLost
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE]);
        $manager->persist($cycle1SatietyLost);

        $cycle1ActionLostRand10 = new ModifierConfig(
            ModifierNameEnum::DISEASE_LOSE_1_PA_PER_CYCLE_RANDOM_10,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $cycle1ActionLostRand10
            ->addCondition($randCondition10)
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE]);
        $manager->persist($cycle1ActionLostRand10);

        $cycle1ActionLostRand16 = new ModifierConfig(
            ModifierNameEnum::DISEASE_LOSE_1_PA_PER_CYCLE_RANDOM_16,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $cycle1ActionLostRand16
            ->addCondition($randCondition16)
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE]);
        $manager->persist($cycle1ActionLostRand16);

        $cycle1ActionLostRand20 = new ModifierConfig(
            ModifierNameEnum::DISEASE_LOSE_1_PA_PER_CYCLE_RANDOM_20,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $cycle1ActionLostRand20
            ->addCondition($randCondition20)
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE]);
        $manager->persist($cycle1ActionLostRand20);

        $cycle1ActionLostRand30 = new ModifierConfig(
            ModifierNameEnum::DISEASE_LOSE_1_PA_PER_CYCLE_RANDOM_30,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $cycle1ActionLostRand30
            ->addCondition($randCondition30)
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE]);
        $manager->persist($cycle1ActionLostRand30);

        $cycle2ActionLostRand40 = new ModifierConfig(
            ModifierNameEnum::DISEASE_LOSE_2_PA_PER_CYCLE_RANDOM_40,
            ModifierReachEnum::PLAYER,
            -2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $cycle2ActionLostRand40
            ->addCondition($randCondition40)
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE]);
        $manager->persist($cycle2ActionLostRand40);

        $cycle1HealthLostRand10 = new ModifierConfig(
            ModifierNameEnum::DISEASE_LOSE_1_HP_PER_CYCLE_RANDOM_10,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::HEALTH_POINT
        );
        $cycle1HealthLostRand10
            ->addCondition($randCondition10)
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE]);
        $manager->persist($cycle1HealthLostRand10);

        $cycle1HealthLostRand16 = new ModifierConfig(
            ModifierNameEnum::DISEASE_LOSE_1_HP_PER_CYCLE_RANDOM_16,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::HEALTH_POINT
        );
        $cycle1HealthLostRand16
            ->addCondition($randCondition16)
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE]);
        $manager->persist($cycle1HealthLostRand16);

        $cycle1HealthLostRand50 = new ModifierConfig(
            ModifierNameEnum::DISEASE_LOSE_1_HP_PER_CYCLE_RANDOM_50,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::HEALTH_POINT
        );
        $cycle1HealthLostRand50
            ->addCondition($randCondition50)
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE]);
        $manager->persist($cycle1HealthLostRand50);

        $cycle1MovementLostRand50 = new ModifierConfig(
            ModifierNameEnum::DISEASE_LOSE_1_PM_PER_CYCLE_RANDOM_50,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $cycle1HealthLostRand50
            ->addCondition($randCondition50)
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE]);
        $manager->persist($cycle1MovementLostRand50);

        $consume1ActionLoss = new ModifierConfig(
            ModifierNameEnum::DISEASE_LOSE_1_PA_AFTER_CONSUMPTION,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $consume1ActionLoss
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [Consume::AFTER_CONSUMPTION]);
        $manager->persist($consume1ActionLoss);

        $consume2ActionLoss = new ModifierConfig(
            ModifierNameEnum::DISEASE_LOSE_2_PA_AFTER_CONSUMPTION,
            ModifierReachEnum::PLAYER,
            -2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $consume2ActionLoss
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [Consume::AFTER_CONSUMPTION]);
        $manager->persist($consume2ActionLoss);

        $infected4HealthLost = new ModifierConfig(
            ModifierNameEnum::DISEASE_LOSE_4_HP_ON_INFECTION,
            ModifierReachEnum::PLAYER,
            -4,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::HEALTH_POINT
        );
        $infected4HealthLost
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [PlayerEvent::INFECTION_PLAYER]);
        $manager->persist($infected4HealthLost);

        $takeCat6HealthLost = new ModifierConfig(
            ModifierNameEnum::DISEASE_LOSE_6_HP_TAKE_CAT,
            ModifierReachEnum::PLAYER,
            -6,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::HEALTH_POINT
        );
        $takeCat6HealthLost
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [
                EquipmentEvent::CHANGE_HOLDER,
                ActionEnum::TAKE,
                ItemEnum::SCHRODINGER
            ]);
        $manager->persist($takeCat6HealthLost);

        $cycle1SatietyIncrease = new ModifierConfig(
            ModifierNameEnum::DISEASE_GAIN_1_SATIETY_PER_CYCLE,
            ModifierReachEnum::PLAYER,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::SATIETY
        );
        $cycle1SatietyIncrease
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE]);
        $manager->persist($cycle1SatietyIncrease);

        $shootAction10PercentAccuracyLost = new ModifierConfig(
            ModifierNameEnum::DISEASE_LOSE_10_SHOOTING_ACCURACY,
            ModifierReachEnum::PLAYER,
            0.9,
            ModifierModeEnum::MULTIPLICATIVE
        );
        foreach (ActionTypeEnum::getShootActions() as $action) {
            $shootAction10PercentAccuracyLost
                ->addTargetEvent(PreparePercentageRollEvent::ACTION_ROLL_RATE, [$action]);
        }
        $manager->persist($shootAction10PercentAccuracyLost);

        $increaseCycleDiseaseChances10 = new ModifierConfig(
            ModifierNameEnum::DISEASE_GAIN_10_CYCLE_DISEASE_CHANCE,
            ModifierReachEnum::PLAYER,
            10,
            ModifierModeEnum::ADDITIVE
        );
        $increaseCycleDiseaseChances10
            ->addTargetEvent(PreparePercentageRollEvent::ACTION_ROLL_RATE, [PlayerEvent::CYCLE_DISEASE]);
        $manager->persist($increaseCycleDiseaseChances10);

        $cycle1ActionLostRand16FitfulSleep = new ModifierConfig(
            ModifierNameEnum::DISEASE_LOSE_1_PA_FITFUL_SLEEP_RANDOM_16,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $cycle1ActionLostRand16FitfulSleep
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE])
            ->addCondition($randCondition16)
            ->addCondition($lyingDownCondition);
        $manager->persist($cycle1ActionLostRand16FitfulSleep);

        $manager->flush();

        $this->addReference(self::REDUCE_MAX_1_HEALTH_POINT, $reduceMax1HealthPoint);
        $this->addReference(self::REDUCE_MAX_2_HEALTH_POINT, $reduceMax2HealthPoint);
        $this->addReference(self::REDUCE_MAX_4_HEALTH_POINT, $reduceMax4HealthPoint);
        $this->addReference(self::REDUCE_MAX_1_MORAL_POINT, $reduceMax1MoralPoint);
        $this->addReference(self::REDUCE_MAX_2_MORAL_POINT, $reduceMax2MoralPoint);
        $this->addReference(self::CYCLE_1_HEALTH_LOST, $cycle1HealthLost);
        $this->addReference(self::CYCLE_2_HEALTH_LOST, $cycle2HealthLost);
        $this->addReference(self::CYCLE_4_HEALTH_LOST, $cycle4HealthLost);
        $this->addReference(self::CYCLE_1_MOVEMENT_LOST, $cycle1MovementLost);
        $this->addReference(self::CYCLE_1_SATIETY_LOST, $cycle1SatietyLost);
        $this->addReference(self::CYCLE_1_ACTION_LOST_RAND_10, $cycle1ActionLostRand10);
        $this->addReference(self::CYCLE_1_HEALTH_LOST_RAND_10, $cycle1HealthLostRand10);
        $this->addReference(self::CYCLE_1_ACTION_LOST_RAND_16, $cycle1ActionLostRand16);
        $this->addReference(self::CYCLE_1_HEALTH_LOST_RAND_16, $cycle1HealthLostRand16);
        $this->addReference(self::CYCLE_1_ACTION_LOST_RAND_16_FITFULL_SLEEP, $cycle1ActionLostRand16FitfulSleep);
        $this->addReference(self::CYCLE_1_ACTION_LOST_RAND_20, $cycle1ActionLostRand20);
        $this->addReference(self::CYCLE_1_ACTION_LOST_RAND_30, $cycle1ActionLostRand30);
        $this->addReference(self::CYCLE_2_ACTION_LOST_RAND_40, $cycle2ActionLostRand40);
        $this->addReference(self::CYCLE_1_MOVEMENT_LOST_RAND_50, $cycle1MovementLostRand50);
        $this->addReference(self::CYCLE_1_HEALTH_LOST_RAND_50, $cycle1HealthLostRand50);
        $this->addReference(self::CONSUME_1_ACTION_LOSS, $consume1ActionLoss);
        $this->addReference(self::CONSUME_2_ACTION_LOSS, $consume2ActionLoss);
        $this->addReference(self::MOVE_INCREASE_MOVEMENT, $moveIncreaseMovement);
        $this->addReference(self::INFECTED_4_HEALTH_LOSS, $infected4HealthLost);
        $this->addReference(self::TAKE_CAT_6_HEALTH_LOSS, $takeCat6HealthLost);
        $this->addReference(self::CYCLE_1_SATIETY_INCREASE, $cycle1SatietyIncrease);
        $this->addReference(self::SHOOT_ACTION_10_PERCENT_ACCURACY_LOST, $shootAction10PercentAccuracyLost);
        $this->addReference(self::INCREASE_CYCLE_DISEASE_CHANCES_10, $increaseCycleDiseaseChances10);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
