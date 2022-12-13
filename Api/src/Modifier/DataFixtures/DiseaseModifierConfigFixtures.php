<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Enum\EventEnum;
use Mush\Modifier\Entity\ModifierCondition;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierConditionEnum;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
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
    public const RANDOM_16 = 'random_16_modifier';
    public const REASON_CONSUME = 'reason_consume';

    public function load(ObjectManager $manager): void
    {
        $randCondition10 = new ModifierCondition(ModifierConditionEnum::RANDOM);
        $randCondition10
            ->setValue(10)
            ->buildName()
        ;
        $manager->persist($randCondition10);

        $randCondition16 = new ModifierCondition(ModifierConditionEnum::RANDOM);
        $randCondition16
            ->setValue(16)
            ->buildName()
        ;
        $manager->persist($randCondition16);

        $randCondition20 = new ModifierCondition(ModifierConditionEnum::RANDOM);
        $randCondition20
            ->setValue(20)
            ->buildName()
        ;
        $manager->persist($randCondition20);

        $randCondition30 = new ModifierCondition(ModifierConditionEnum::RANDOM);
        $randCondition30
            ->setValue(30)
            ->buildName()
        ;
        $manager->persist($randCondition30);

        $randCondition40 = new ModifierCondition(ModifierConditionEnum::RANDOM);
        $randCondition40
            ->setValue(40)
            ->buildName()
        ;
        $manager->persist($randCondition40);

        $randCondition50 = new ModifierCondition(ModifierConditionEnum::RANDOM);
        $randCondition50
            ->setValue(50)
            ->buildName()
        ;
        $manager->persist($randCondition50);

        $lyingDownCondition = new ModifierCondition(ModifierConditionEnum::PLAYER_STATUS);
        $lyingDownCondition
            ->setCondition(PlayerStatusEnum::LYING_DOWN)
            ->buildName()
        ;
        $manager->persist($lyingDownCondition);

        $moveIncreaseMovement = new ModifierConfig();
        $moveIncreaseMovement
            ->setScope(ActionEnum::MOVE)
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->buildName()
        ;
        $manager->persist($moveIncreaseMovement);

        $reduceMax1HealthPoint = new ModifierConfig();
        $reduceMax1HealthPoint
            ->setScope(ModifierScopeEnum::MAX_POINT)
            ->setTarget(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->buildName()
        ;
        $manager->persist($reduceMax1HealthPoint);

        $reduceMax2HealthPoint = new ModifierConfig();
        $reduceMax2HealthPoint
            ->setScope(ModifierScopeEnum::MAX_POINT)
            ->setTarget(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-2)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->buildName()
        ;
        $manager->persist($reduceMax2HealthPoint);

        $reduceMax4HealthPoint = new ModifierConfig();
        $reduceMax4HealthPoint
            ->setScope(ModifierScopeEnum::MAX_POINT)
            ->setTarget(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-4)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->buildName()
        ;
        $manager->persist($reduceMax4HealthPoint);

        $reduceMax1MoralPoint = new ModifierConfig();
        $reduceMax1MoralPoint
            ->setScope(ModifierScopeEnum::MAX_POINT)
            ->setTarget(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->buildName()
        ;
        $manager->persist($reduceMax1MoralPoint);

        $reduceMax2MoralPoint = new ModifierConfig();
        $reduceMax2MoralPoint
            ->setScope(ModifierScopeEnum::MAX_POINT)
            ->setTarget(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-2)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->buildName()
        ;
        $manager->persist($reduceMax2MoralPoint);

        $cycle1HealthLost = new ModifierConfig();
        $cycle1HealthLost
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->buildName()
        ;
        $manager->persist($cycle1HealthLost);

        $cycle2HealthLost = new ModifierConfig();
        $cycle2HealthLost
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-2)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->buildName()
        ;
        $manager->persist($cycle2HealthLost);

        $cycle4HealthLost = new ModifierConfig();
        $cycle4HealthLost
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-4)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->buildName()
        ;
        $manager->persist($cycle4HealthLost);

        $cycle1MovementLost = new ModifierConfig();
        $cycle1MovementLost
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->buildName()
        ;
        $manager->persist($cycle1MovementLost);

        $cycle1SatietyLost = new ModifierConfig();
        $cycle1SatietyLost
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::SATIETY)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->buildName()
        ;
        $manager->persist($cycle1SatietyLost);

        $cycle1ActionLostRand10 = new ModifierConfig();
        $cycle1ActionLostRand10
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($randCondition10)
            ->buildName()
        ;
        $manager->persist($cycle1ActionLostRand10);

        $cycle1HealthLostRand10 = new ModifierConfig();
        $cycle1HealthLostRand10
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($randCondition10)
            ->buildName()
        ;
        $manager->persist($cycle1HealthLostRand10);

        $cycle1ActionLostRand16 = new ModifierConfig();
        $cycle1ActionLostRand16
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($randCondition16)
            ->buildName()
        ;
        $manager->persist($cycle1ActionLostRand16);

        $cycle1HealthLostRand16 = new ModifierConfig();
        $cycle1HealthLostRand16
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($randCondition16)
            ->buildName()
        ;
        $manager->persist($cycle1HealthLostRand16);

        $cycle1ActionLostRand20 = new ModifierConfig();
        $cycle1ActionLostRand20
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($randCondition20)
            ->buildName()
        ;
        $manager->persist($cycle1ActionLostRand20);

        $cycle1ActionLostRand30 = new ModifierConfig();
        $cycle1ActionLostRand30
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($randCondition30)
            ->buildName()
        ;
        $manager->persist($cycle1ActionLostRand30);

        $cycle2ActionLostRand40 = new ModifierConfig();
        $cycle2ActionLostRand40
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-2)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($randCondition40)
            ->buildName()
        ;
        $manager->persist($cycle2ActionLostRand40);

        $cycle1MovementLostRand50 = new ModifierConfig();
        $cycle1MovementLostRand50
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($randCondition50)
            ->buildName()
        ;
        $manager->persist($cycle1MovementLostRand50);

        $cycle1HealthLostRand50 = new ModifierConfig();
        $cycle1HealthLostRand50
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($randCondition50)
            ->buildName()
        ;
        $manager->persist($cycle1HealthLostRand50);

        $consumeActionCondition = new ModifierCondition(ModifierConditionEnum::REASON);
        $consumeActionCondition
            ->setCondition(ActionEnum::CONSUME)
            ->buildName()
        ;
        $manager->persist($consumeActionCondition);

        $consume1ActionLoss = new ModifierConfig();
        $consume1ActionLoss
            ->setScope(ActionEvent::POST_ACTION)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($consumeActionCondition)
            ->buildName()
        ;
        $manager->persist($consume1ActionLoss);

        $consume2ActionLoss = new ModifierConfig();
        $consume2ActionLoss
            ->setScope(ActionEvent::POST_ACTION)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-2)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($consumeActionCondition)
            ->buildName()
        ;
        $manager->persist($consume2ActionLoss);

        $infected4HealthLost = new ModifierConfig();
        $infected4HealthLost
            ->setScope(PlayerEvent::INFECTION_PLAYER)
            ->setTarget(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-4)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->buildName()
        ;
        $manager->persist($infected4HealthLost);

        $takeCatCondition = new ModifierCondition(ModifierConditionEnum::PLAYER_EQUIPMENT);
        $takeCatCondition
            ->setCondition(ItemEnum::SCHRODINGER)
            ->buildName()
        ;
        $manager->persist($takeCatCondition);

        $takeCat6HealthLost = new ModifierConfig();
        $takeCat6HealthLost
            ->setScope(ActionEvent::POST_ACTION)
            ->setTarget(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-6)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($takeCatCondition)
            ->buildName()
        ;
        $manager->persist($takeCat6HealthLost);

        $cycle1SatietyIncrease = new ModifierConfig();
        $cycle1SatietyIncrease
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::SATIETY)
            ->setDelta(1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->buildName()
        ;
        $manager->persist($cycle1SatietyIncrease);

        $shootAction10PercentAccuracyLost = new ModifierConfig();
        $shootAction10PercentAccuracyLost
            ->setScope(ActionTypeEnum::ACTION_SHOOT)
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(0.9)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::MULTIPLICATIVE)
            ->buildName()
        ;
        $manager->persist($shootAction10PercentAccuracyLost);

        $increaseCycleDiseaseChances10 = new ModifierConfig();
        $increaseCycleDiseaseChances10
            ->setScope(PlayerEvent::CYCLE_DISEASE)
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(10)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->buildName()
        ;
        $manager->persist($increaseCycleDiseaseChances10);

        $cycle1ActionLostRand16FitfullSleep = new ModifierConfig();
        $cycle1ActionLostRand16FitfullSleep
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setModifierName(ModifierNameEnum::FITFULL_SLEEP)
            ->addModifierCondition($randCondition16)
            ->addModifierCondition($lyingDownCondition)
            ->buildName()
        ;
        $manager->persist($cycle1ActionLostRand16FitfullSleep);

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
        $this->addReference(self::CYCLE_1_ACTION_LOST_RAND_16_FITFULL_SLEEP, $cycle1ActionLostRand16FitfullSleep);
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
        $this->addReference(self::RANDOM_16, $randCondition16);
        $this->addReference(self::REASON_CONSUME, $consumeActionCondition);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
