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
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
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
    public const CONSUME_DRUG_1_ACTION_LOSS = 'consume_drug_1_action_loss';
    public const CONSUME_DRUG_2_ACTION_LOSS = 'consume_drug_2_action_loss';
    public const SHOOT_ACTION_10_PERCENT_ACCURACY_LOST = 'shoot_action_10_percent_accuracy_lost';
    public const MOVE_INCREASE_MOVEMENT = 'move_increase_movement';
    public const TAKE_CAT_6_HEALTH_LOSS = 'take_cat_6_health_loss';
    public const INFECTED_4_HEALTH_LOSS = 'infected_4_health_loss';
    public const INCREASE_CYCLE_DISEASE_CHANCES_10 = 'increase_cycle_disease_chances_10';
    public const RANDOM_16 = 'random_16_modifier';
    public const REASON_CONSUME = 'reason_consume';

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

        $lyingDownActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::PLAYER_STATUS);
        $lyingDownActivationRequirement
            ->setActivationRequirement(PlayerStatusEnum::LYING_DOWN)
            ->buildName()
        ;
        $manager->persist($lyingDownActivationRequirement);

        $moveIncreaseMovement = new VariableEventModifierConfig();
        $moveIncreaseMovement
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setDelta(1)
            ->setTargetEvent(ActionEnum::MOVE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $moveIncreaseMovement->buildName();
        $manager->persist($moveIncreaseMovement);

        $reduceMax1HealthPoint = new VariableEventModifierConfig();
        $reduceMax1HealthPoint
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::MAX_POINT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $reduceMax1HealthPoint->buildName();
        $manager->persist($reduceMax1HealthPoint);

        $reduceMax2HealthPoint = new VariableEventModifierConfig();
        $reduceMax2HealthPoint
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::MAX_POINT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $reduceMax2HealthPoint->buildName();
        $manager->persist($reduceMax2HealthPoint);

        $reduceMax4HealthPoint = new VariableEventModifierConfig();
        $reduceMax4HealthPoint
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-4)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::MAX_POINT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $reduceMax4HealthPoint->buildName();
        $manager->persist($reduceMax4HealthPoint);

        $reduceMax1MoralPoint = new VariableEventModifierConfig();
        $reduceMax1MoralPoint
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::MAX_POINT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $reduceMax1MoralPoint->buildName();
        $manager->persist($reduceMax1MoralPoint);

        $reduceMax2MoralPoint = new VariableEventModifierConfig();
        $reduceMax2MoralPoint
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::MAX_POINT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $reduceMax2MoralPoint->buildName();
        $manager->persist($reduceMax2MoralPoint);

        $cycle1HealthLost = new VariableEventModifierConfig();
        $cycle1HealthLost
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $cycle1HealthLost->buildName();
        $manager->persist($cycle1HealthLost);

        $cycle2HealthLost = new VariableEventModifierConfig();
        $cycle2HealthLost
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-2)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $cycle2HealthLost->buildName();
        $manager->persist($cycle2HealthLost);

        $cycle4HealthLost = new VariableEventModifierConfig();
        $cycle4HealthLost
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-4)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $cycle4HealthLost->buildName();
        $manager->persist($cycle4HealthLost);

        $cycle1MovementLost = new VariableEventModifierConfig();
        $cycle1MovementLost
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $cycle1MovementLost->buildName();
        $manager->persist($cycle1MovementLost);

        $cycle1SatietyLost = new VariableEventModifierConfig();
        $cycle1SatietyLost
            ->setTargetVariable(PlayerVariableEnum::SATIETY)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $cycle1SatietyLost->buildName();
        $manager->persist($cycle1SatietyLost);

        $cycle1ActionLostRand10 = new VariableEventModifierConfig();
        $cycle1ActionLostRand10
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->addModifierRequirement($randActivationRequirement10)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $cycle1ActionLostRand10->buildName();
        $manager->persist($cycle1ActionLostRand10);

        $cycle1HealthLostRand10 = new VariableEventModifierConfig();
        $cycle1HealthLostRand10
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->addModifierRequirement($randActivationRequirement10)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $cycle1HealthLostRand10->buildName();
        $manager->persist($cycle1HealthLostRand10);

        $cycle1ActionLostRand16 = new VariableEventModifierConfig();
        $cycle1ActionLostRand16
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->addModifierRequirement($randActivationRequirement16)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $cycle1ActionLostRand16->buildName();
        $manager->persist($cycle1ActionLostRand16);

        $cycle1HealthLostRand16 = new VariableEventModifierConfig();
        $cycle1HealthLostRand16
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->addModifierRequirement($randActivationRequirement16)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $cycle1HealthLostRand16->buildName();
        $manager->persist($cycle1HealthLostRand16);

        $cycle1ActionLostRand20 = new VariableEventModifierConfig();
        $cycle1ActionLostRand20
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->addModifierRequirement($randActivationRequirement20)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $cycle1ActionLostRand20->buildName();
        $manager->persist($cycle1ActionLostRand20);

        $cycle1ActionLostRand30 = new VariableEventModifierConfig();
        $cycle1ActionLostRand30
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->addModifierRequirement($randActivationRequirement30)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $cycle1ActionLostRand30->buildName();
        $manager->persist($cycle1ActionLostRand30);

        $cycle2ActionLostRand40 = new VariableEventModifierConfig();
        $cycle2ActionLostRand40
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-2)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->addModifierRequirement($randActivationRequirement40)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $cycle2ActionLostRand40->buildName();
        $manager->persist($cycle2ActionLostRand40);

        $cycle1MovementLostRand50 = new VariableEventModifierConfig();
        $cycle1MovementLostRand50
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->addModifierRequirement($randActivationRequirement50)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $cycle1MovementLostRand50->buildName();
        $manager->persist($cycle1MovementLostRand50);

        $cycle1HealthLostRand50 = new VariableEventModifierConfig();
        $cycle1HealthLostRand50
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->addModifierRequirement($randActivationRequirement50)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $cycle1HealthLostRand50->buildName();
        $manager->persist($cycle1HealthLostRand50);

        $consumeActionActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::REASON);
        $consumeActionActivationRequirement
            ->setActivationRequirement(ActionEnum::CONSUME)
            ->buildName()
        ;
        $manager->persist($consumeActionActivationRequirement);

        $comsumeDrugActionActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::REASON);
        $comsumeDrugActionActivationRequirement
            ->setActivationRequirement(ActionEnum::CONSUME_DRUG)
            ->buildName()
        ;
        $manager->persist($comsumeDrugActionActivationRequirement);

        $consume1ActionLoss = new VariableEventModifierConfig();
        $consume1ActionLoss
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->addModifierRequirement($consumeActionActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $consume1ActionLoss->buildName();
        $manager->persist($consume1ActionLoss);

        $consume2ActionLoss = new VariableEventModifierConfig();
        $consume2ActionLoss
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-2)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->addModifierRequirement($consumeActionActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $consume2ActionLoss->buildName();
        $manager->persist($consume2ActionLoss);

        $consumeDrug1ActionLoss = new VariableEventModifierConfig();
        $consumeDrug1ActionLoss
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->addModifierRequirement($comsumeDrugActionActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $consumeDrug1ActionLoss->buildName();
        $manager->persist($consumeDrug1ActionLoss);

        $consumeDrug2ActionLoss = new VariableEventModifierConfig();
        $consumeDrug2ActionLoss
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-2)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->addModifierRequirement($comsumeDrugActionActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $consumeDrug2ActionLoss->buildName();
        $manager->persist($consumeDrug2ActionLoss);

        $infected4HealthLost = new VariableEventModifierConfig();
        $infected4HealthLost
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-4)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(PlayerEvent::INFECTION_PLAYER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $infected4HealthLost->buildName();
        $manager->persist($infected4HealthLost);

        $takeCatActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::PLAYER_EQUIPMENT);
        $takeCatActivationRequirement
            ->setActivationRequirement(ItemEnum::SCHRODINGER)
            ->buildName()
        ;
        $manager->persist($takeCatActivationRequirement);

        $takeCat6HealthLost = new VariableEventModifierConfig();
        $takeCat6HealthLost
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-6)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->addModifierRequirement($takeCatActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $takeCat6HealthLost->buildName();
        $manager->persist($takeCat6HealthLost);

        $cycle1SatietyIncrease = new VariableEventModifierConfig();
        $cycle1SatietyIncrease
            ->setTargetVariable(PlayerVariableEnum::SATIETY)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $cycle1SatietyIncrease->buildName();
        $manager->persist($cycle1SatietyIncrease);

        $shootAction10PercentAccuracyLost = new VariableEventModifierConfig();
        $shootAction10PercentAccuracyLost
            ->setTargetVariable(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(0.9)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionTypeEnum::ACTION_SHOOT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $shootAction10PercentAccuracyLost->buildName();
        $manager->persist($shootAction10PercentAccuracyLost);

        $increaseCycleDiseaseChances10 = new VariableEventModifierConfig();
        $increaseCycleDiseaseChances10
            ->setTargetVariable(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(10)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(PlayerEvent::CYCLE_DISEASE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $increaseCycleDiseaseChances10->buildName();
        $manager->persist($increaseCycleDiseaseChances10);

        $cycle1ActionLostRand16FitfullSleep = new VariableEventModifierConfig();
        $cycle1ActionLostRand16FitfullSleep
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->addModifierRequirement($randActivationRequirement16)
            ->addModifierRequirement($lyingDownActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(ModifierNameEnum::FITFULL_SLEEP)
        ;
        $cycle1ActionLostRand16FitfullSleep->buildName();
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
        $this->addReference(self::CONSUME_DRUG_1_ACTION_LOSS, $consumeDrug1ActionLoss);
        $this->addReference(self::CONSUME_DRUG_2_ACTION_LOSS, $consumeDrug2ActionLoss);
        $this->addReference(self::MOVE_INCREASE_MOVEMENT, $moveIncreaseMovement);
        $this->addReference(self::INFECTED_4_HEALTH_LOSS, $infected4HealthLost);
        $this->addReference(self::TAKE_CAT_6_HEALTH_LOSS, $takeCat6HealthLost);
        $this->addReference(self::CYCLE_1_SATIETY_INCREASE, $cycle1SatietyIncrease);
        $this->addReference(self::SHOOT_ACTION_10_PERCENT_ACCURACY_LOST, $shootAction10PercentAccuracyLost);
        $this->addReference(self::INCREASE_CYCLE_DISEASE_CHANCES_10, $increaseCycleDiseaseChances10);
        $this->addReference(self::RANDOM_16, $randActivationRequirement16);
        $this->addReference(self::REASON_CONSUME, $consumeActionActivationRequirement);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
