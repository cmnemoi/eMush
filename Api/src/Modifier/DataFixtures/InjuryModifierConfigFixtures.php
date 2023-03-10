<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Player\Enum\PlayerVariableEnum;

class InjuryModifierConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const NOT_MOVE_ACTION_1_INCREASE = 'not_move_action_1_increase';
    public const NOT_MOVE_ACTION_2_INCREASE = 'not_move_action_2_increase';
    public const NOT_MOVE_ACTION_3_INCREASE = 'not_move_action_3_increase';
    public const REDUCE_MAX_3_MOVEMENT_POINT = 'reduce_max_3_movement_point';
    public const REDUCE_MAX_5_MOVEMENT_POINT = 'reduce_max_5_movement_point';
    public const REDUCE_MAX_12_MOVEMENT_POINT = 'reduce_max_12_movement_point';
    public const SHOOT_ACTION_15_PERCENT_ACCURACY_LOST = 'shoot_action_15_percent_accuracy_lost';
    public const SHOOT_ACTION_20_PERCENT_ACCURACY_LOST = 'shoot_action_20_percent_accuracy_lost';
    public const SHOOT_ACTION_40_PERCENT_ACCURACY_LOST = 'shoot_action_40_percent_accuracy_lost';

    public function load(ObjectManager $manager): void
    {
        /** @var ModifierActivationRequirement $notConsumeActionActivationRequirement */
        $notConsumeActionActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::NOT_REASON);
        $notConsumeActionActivationRequirement
            ->setActivationRequirement(ActionEnum::CONSUME)
            ->buildName()
        ;
        $manager->persist($notConsumeActionActivationRequirement);

        /** @var ModifierActivationRequirement $notConsumeDrugActionActivationRequirement */
        $notConsumeDrugActionActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::NOT_REASON);
        $notConsumeDrugActionActivationRequirement
            ->setActivationRequirement(ActionEnum::CONSUME_DRUG)
            ->buildName()
        ;
        $manager->persist($notConsumeDrugActionActivationRequirement);

        /** @var ModifierActivationRequirement $notSurgeryActionActivationRequirement */
        $notSurgeryActionActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::NOT_REASON);
        $notSurgeryActionActivationRequirement
            ->setActivationRequirement(ActionEnum::SURGERY)
            ->buildName()
        ;
        $manager->persist($notSurgeryActionActivationRequirement);

        /** @var ModifierActivationRequirement $notSelfSurgeryActivationRequirement */
        $notSelfSurgeryActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::NOT_REASON);
        $notSelfSurgeryActivationRequirement
            ->setActivationRequirement(ActionEnum::SELF_SURGERY)
            ->buildName()
        ;
        $manager->persist($notSelfSurgeryActivationRequirement);

        /** @var ModifierActivationRequirement $notMoveActionActivationRequirement */
        $notMoveActionActivationRequirement = $this->getReference(DisorderModifierConfigFixtures::NOT_REASON_MOVE);

        $notMoveAction1Increase = new VariableEventModifierConfig();
        $notMoveAction1Increase
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::ACTIONS)
            ->addModifierRequirement($notMoveActionActivationRequirement)
            ->addModifierRequirement($notConsumeActionActivationRequirement)
            ->addModifierRequirement($notConsumeDrugActionActivationRequirement)
            ->addModifierRequirement($notSurgeryActionActivationRequirement)
            ->addModifierRequirement($notSelfSurgeryActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $notMoveAction1Increase->buildName();
        $manager->persist($notMoveAction1Increase);

        $notMoveAction2Increase = new VariableEventModifierConfig();
        $notMoveAction2Increase
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::ACTIONS)
            ->addModifierRequirement($notMoveActionActivationRequirement)
            ->addModifierRequirement($notConsumeActionActivationRequirement)
            ->addModifierRequirement($notConsumeDrugActionActivationRequirement)
            ->addModifierRequirement($notSurgeryActionActivationRequirement)
            ->addModifierRequirement($notSelfSurgeryActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $notMoveAction2Increase->buildName();
        $manager->persist($notMoveAction2Increase);

        $notMoveAction3Increase = new VariableEventModifierConfig();
        $notMoveAction3Increase
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(3)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::ACTIONS)
            ->addModifierRequirement($notMoveActionActivationRequirement)
            ->addModifierRequirement($notConsumeActionActivationRequirement)
            ->addModifierRequirement($notConsumeDrugActionActivationRequirement)
            ->addModifierRequirement($notSurgeryActionActivationRequirement)
            ->addModifierRequirement($notSelfSurgeryActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $notMoveAction3Increase->buildName();
        $manager->persist($notMoveAction3Increase);

        $reduceMax3MovementPoint = new VariableEventModifierConfig();
        $reduceMax3MovementPoint
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-3)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::MAX_POINT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $reduceMax3MovementPoint->buildName();
        $manager->persist($reduceMax3MovementPoint);

        $reduceMax5MovementPoint = new VariableEventModifierConfig();
        $reduceMax5MovementPoint
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-5)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::MAX_POINT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $reduceMax5MovementPoint->buildName();
        $manager->persist($reduceMax5MovementPoint);

        $reduceMax12MovementPoint = new VariableEventModifierConfig();
        $reduceMax12MovementPoint
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-12)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::MAX_POINT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $reduceMax12MovementPoint->buildName();
        $manager->persist($reduceMax12MovementPoint);

        $shootAction15PercentAccuracyLost = new VariableEventModifierConfig();
        $shootAction15PercentAccuracyLost
            ->setTargetVariable(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(0.85)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionTypeEnum::ACTION_SHOOT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $shootAction15PercentAccuracyLost->buildName();
        $manager->persist($shootAction15PercentAccuracyLost);

        $shootAction20PercentAccuracyLost = new VariableEventModifierConfig();
        $shootAction20PercentAccuracyLost
            ->setTargetVariable(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(0.80)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionTypeEnum::ACTION_SHOOT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $shootAction20PercentAccuracyLost->buildName();
        $manager->persist($shootAction20PercentAccuracyLost);

        $shootAction40PercentAccuracyLost = new VariableEventModifierConfig();
        $shootAction40PercentAccuracyLost
            ->setTargetVariable(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(0.60)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionTypeEnum::ACTION_SHOOT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $shootAction40PercentAccuracyLost->buildName();
        $manager->persist($shootAction40PercentAccuracyLost);

        $manager->flush();

        $this->addReference(self::NOT_MOVE_ACTION_1_INCREASE, $notMoveAction1Increase);
        $this->addReference(self::NOT_MOVE_ACTION_2_INCREASE, $notMoveAction2Increase);
        $this->addReference(self::NOT_MOVE_ACTION_3_INCREASE, $notMoveAction3Increase);
        $this->addReference(self::REDUCE_MAX_3_MOVEMENT_POINT, $reduceMax3MovementPoint);
        $this->addReference(self::REDUCE_MAX_5_MOVEMENT_POINT, $reduceMax5MovementPoint);
        $this->addReference(self::REDUCE_MAX_12_MOVEMENT_POINT, $reduceMax12MovementPoint);
        $this->addReference(self::SHOOT_ACTION_15_PERCENT_ACCURACY_LOST, $shootAction15PercentAccuracyLost);
        $this->addReference(self::SHOOT_ACTION_20_PERCENT_ACCURACY_LOST, $shootAction20PercentAccuracyLost);
        $this->addReference(self::SHOOT_ACTION_40_PERCENT_ACCURACY_LOST, $shootAction40PercentAccuracyLost);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            DisorderModifierConfigFixtures::class,
        ];
    }
}
