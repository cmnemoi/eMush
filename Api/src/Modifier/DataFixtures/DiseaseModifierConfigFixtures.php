<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\EventConfigFixtures;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Game\Event\RollPercentageEvent;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerCycleEvent;
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
    public const CYCLE_1_ACTION_LOST_RAND_16_FITFUL_SLEEP = 'cycle_1_action_lost_rand_16_fitful_sleep';
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
    public const RANDOM_40 = 'random_40_modifier';
    public const RANDOM_50 = 'random_50_modifier';

    public function load(ObjectManager $manager): void
    {
        $randActivationRequirement10 = new ModifierActivationRequirement(ModifierRequirementEnum::RANDOM);
        $randActivationRequirement10
            ->setValue(10)
            ->buildName()
        ;
        $manager->persist($randActivationRequirement10);

        $randActivationRequirement16 = new ModifierActivationRequirement(ModifierRequirementEnum::RANDOM);
        $randActivationRequirement16
            ->setValue(16)
            ->buildName()
        ;
        $manager->persist($randActivationRequirement16);

        $randActivationRequirement20 = new ModifierActivationRequirement(ModifierRequirementEnum::RANDOM);
        $randActivationRequirement20
            ->setValue(20)
            ->buildName()
        ;
        $manager->persist($randActivationRequirement20);

        $randActivationRequirement30 = new ModifierActivationRequirement(ModifierRequirementEnum::RANDOM);
        $randActivationRequirement30
            ->setValue(30)
            ->buildName()
        ;
        $manager->persist($randActivationRequirement30);

        $randActivationRequirement40 = new ModifierActivationRequirement(ModifierRequirementEnum::RANDOM);
        $randActivationRequirement40
            ->setValue(40)
            ->buildName()
        ;
        $manager->persist($randActivationRequirement40);

        $randActivationRequirement50 = new ModifierActivationRequirement(ModifierRequirementEnum::RANDOM);
        $randActivationRequirement50
            ->setValue(50)
            ->buildName()
        ;
        $manager->persist($randActivationRequirement50);

        $lyingDownActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::HOLDER_HAS_STATUS);
        $lyingDownActivationRequirement
            ->setActivationRequirement(PlayerStatusEnum::LYING_DOWN)
            ->buildName()
        ;
        $manager->persist($lyingDownActivationRequirement);

        $moveIncreaseMovement = new VariableEventModifierConfig('increased1MovementCostOnActions');
        $moveIncreaseMovement
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setDelta(1)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([ActionEnum::MOVE => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($moveIncreaseMovement);

        /** @var AbstractEventConfig $eventConfigLose1Health */
        $eventConfigLose1Health = $this->getReference(EventConfigFixtures::MAX_HEALTH_REDUCE_1);
        $reduceMax1HealthPoint = new DirectModifierConfig('maxHealthReduce1');
        $reduceMax1HealthPoint
            ->setTriggeredEvent($eventConfigLose1Health)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($reduceMax1HealthPoint);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::MAX_HEALTH_REDUCE_2);
        $reduceMax2HealthPoint = new DirectModifierConfig('maxHealthReduce2');
        $reduceMax2HealthPoint
            ->setTriggeredEvent($eventConfig)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($reduceMax2HealthPoint);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::MAX_HEALTH_REDUCE_4);
        $reduceMax4HealthPoint = new DirectModifierConfig('maxHealthReduce4');
        $reduceMax4HealthPoint
            ->setTriggeredEvent($eventConfig)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($reduceMax4HealthPoint);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::MAX_MORAL_REDUCE_1);
        $reduceMax1MoralPoint = new DirectModifierConfig('maxMoralReduce1');
        $reduceMax1MoralPoint
            ->setTriggeredEvent($eventConfig)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($reduceMax1MoralPoint);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::MAX_MORAL_REDUCE_2);
        $reduceMax2MoralPoint = new DirectModifierConfig('maxMoralReduce2');
        $reduceMax2MoralPoint
            ->setTriggeredEvent($eventConfig)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($reduceMax2MoralPoint);

        /** @var AbstractEventConfig $eventConfig1HealthReduce */
        $eventConfig1HealthReduce = $this->getReference(EventConfigFixtures::HEALTH_REDUCE_1);
        $cycle1HealthLost = new TriggerEventModifierConfig('cycle1HealthLoss');
        $cycle1HealthLost
            ->setTriggeredEvent($eventConfig1HealthReduce)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setApplyOnTarget(true)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($cycle1HealthLost);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::HEALTH_REDUCE_2);
        $cycle2HealthLost = new TriggerEventModifierConfig('cycle2HealthLoss');
        $cycle2HealthLost
            ->setTriggeredEvent($eventConfig)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setApplyOnTarget(true)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($cycle2HealthLost);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::HEALTH_REDUCE_4);
        $cycle4HealthLost = new TriggerEventModifierConfig('cycle4HealthLoss');
        $cycle4HealthLost
            ->setTriggeredEvent($eventConfig)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setApplyOnTarget(true)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($cycle4HealthLost);

        /** @var AbstractEventConfig $eventConfigMovementLose1 */
        $eventConfigMovementLose1 = $this->getReference(EventConfigFixtures::MOVEMENT_REDUCE_1);
        $cycle1MovementLost = new TriggerEventModifierConfig('cycle1MovementLoss');
        $cycle1MovementLost
            ->setTriggeredEvent($eventConfigMovementLose1)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setApplyOnTarget(true)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($cycle1MovementLost);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::SATIETY_REDUCE_1);
        $cycle1SatietyLost = new TriggerEventModifierConfig('cycle1SatietyLoss');
        $cycle1SatietyLost
            ->setTriggeredEvent($eventConfig)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setApplyOnTarget(true)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($cycle1SatietyLost);

        /** @var AbstractEventConfig $eventConfigLose1Action */
        $eventConfigLose1Action = $this->getReference(EventConfigFixtures::ACTION_REDUCE_1);

        $cycle1ActionLostRand10 = new TriggerEventModifierConfig('cycle1ActionLostRand10');
        $cycle1ActionLostRand10
            ->setTriggeredEvent($eventConfigLose1Action)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setApplyOnTarget(true)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->addModifierRequirement($randActivationRequirement10)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($cycle1ActionLostRand10);

        $cycle1HealthLostRand10 = new TriggerEventModifierConfig('cycle1HealthLostRand10');
        $cycle1HealthLostRand10
            ->setTriggeredEvent($eventConfig1HealthReduce)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setApplyOnTarget(true)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->addModifierRequirement($randActivationRequirement10)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($cycle1HealthLostRand10);

        $cycle1ActionLostRand16 = new TriggerEventModifierConfig('cycle1ActionLostRand16');
        $cycle1ActionLostRand16
            ->setTriggeredEvent($eventConfigLose1Action)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setApplyOnTarget(true)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->addModifierRequirement($randActivationRequirement16)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($cycle1ActionLostRand16);

        $cycle1HealthLostRand16 = new TriggerEventModifierConfig('cycle1HealthLostRand16');
        $cycle1HealthLostRand16
            ->setTriggeredEvent($eventConfigLose1Health)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setApplyOnTarget(true)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->addModifierRequirement($randActivationRequirement16)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($cycle1HealthLostRand16);

        $cycle1ActionLostRand20 = new TriggerEventModifierConfig('cycle1ActionLostRand20');
        $cycle1ActionLostRand20
            ->setTriggeredEvent($eventConfigLose1Action)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setApplyOnTarget(true)
            ->addModifierRequirement($randActivationRequirement20)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($cycle1ActionLostRand20);

        $cycle1ActionLostRand30 = new TriggerEventModifierConfig('cycle1ActionLostRand30');
        $cycle1ActionLostRand30
            ->setTriggeredEvent($eventConfigLose1Action)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setApplyOnTarget(true)
            ->addModifierRequirement($randActivationRequirement30)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($cycle1ActionLostRand30);

        /** @var AbstractEventConfig $eventConfigLose2Action */
        $eventConfigLose2Action = $this->getReference(EventConfigFixtures::ACTION_REDUCE_2);

        $cycle2ActionLostRand40 = new TriggerEventModifierConfig('cycle2ActionLostRand40');
        $cycle2ActionLostRand40
            ->setTriggeredEvent($eventConfigLose2Action)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setApplyOnTarget(true)
            ->addModifierRequirement($randActivationRequirement40)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($cycle2ActionLostRand40);

        $cycle1MovementLostRand50 = new TriggerEventModifierConfig('cycle1MovementLostRand50');
        $cycle1MovementLostRand50
            ->setTriggeredEvent($eventConfigMovementLose1)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setApplyOnTarget(true)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->addModifierRequirement($randActivationRequirement50)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($cycle1MovementLostRand50);

        $cycle1HealthLostRand50 = new TriggerEventModifierConfig('cycle1HealthLostRand50');
        $cycle1HealthLostRand50
            ->setTriggeredEvent($eventConfigLose1Health)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setApplyOnTarget(true)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->addModifierRequirement($randActivationRequirement50)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($cycle1HealthLostRand50);

        $consume1ActionLoss = new TriggerEventModifierConfig('consume1ActionLoss');
        $consume1ActionLoss
            ->setTriggeredEvent($eventConfigLose1Action)
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setTagConstraints([
                ActionEnum::CONSUME_DRUG => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::CONSUME => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($consume1ActionLoss);

        $consume2ActionLoss = new TriggerEventModifierConfig('consume2ActionLoss');
        $consume2ActionLoss
            ->setTriggeredEvent($eventConfigLose2Action)
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setTagConstraints([
                ActionEnum::CONSUME_DRUG => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::CONSUME => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($consume2ActionLoss);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::HEALTH_REDUCE_4);
        $infected4HealthLost = new TriggerEventModifierConfig('infected4HealthLost');
        $infected4HealthLost
            ->setTriggeredEvent($eventConfig)
            ->setTargetEvent(PlayerEvent::INFECTION_PLAYER)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setApplyOnTarget(true)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($infected4HealthLost);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::HEALTH_REDUCE_6);
        $takeCat6HealthLost = new TriggerEventModifierConfig('takeCat6HealthLost');
        $takeCat6HealthLost
            ->setTriggeredEvent($eventConfig)
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setTagConstraints([ItemEnum::SCHRODINGER => ModifierRequirementEnum::ALL_TAGS, ActionEnum::TAKE => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($takeCat6HealthLost);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::SATIETY_INCREASE_1);
        $cycle1SatietyIncrease = new TriggerEventModifierConfig('cycle1SatietyIncrease');
        $cycle1SatietyIncrease
            ->setTriggeredEvent($eventConfig)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setApplyOnTarget(true)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($cycle1SatietyIncrease);

        $shootAction10PercentAccuracyLost = new VariableEventModifierConfig('decrease10PercentShootPercentage');
        $shootAction10PercentAccuracyLost
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(0.9)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setTagConstraints([
                ActionEnum::SHOOT => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($shootAction10PercentAccuracyLost);

        $increaseCycleDiseaseChances10 = new VariableEventModifierConfig('increase10PercentShootPercentage');
        $increaseCycleDiseaseChances10
            ->setTargetVariable(RollPercentageEvent::ROLL_PERCENTAGE)
            ->setDelta(10)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setApplyOnTarget(false)
            ->setTagConstraints([
                PlayerEvent::CYCLE_DISEASE => ModifierRequirementEnum::ALL_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($increaseCycleDiseaseChances10);

        $cycle1ActionLostRand16FitfulSleep = new TriggerEventModifierConfig('cycle1ActionLostRand16FitfulSleep');
        $cycle1ActionLostRand16FitfulSleep
            ->setTriggeredEvent($eventConfigLose1Action)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setApplyOnTarget(true)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->addModifierRequirement($randActivationRequirement16)
            ->addModifierRequirement($lyingDownActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(ModifierNameEnum::FITFUL_SLEEP)
        ;
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
        $this->addReference(self::CYCLE_1_ACTION_LOST_RAND_16_FITFUL_SLEEP, $cycle1ActionLostRand16FitfulSleep);
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
        $this->addReference(self::RANDOM_16, $randActivationRequirement16);
        $this->addReference(self::RANDOM_50, $randActivationRequirement50);
        $this->addReference(self::RANDOM_40, $randActivationRequirement40);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            EventConfigFixtures::class,
        ];
    }
}
