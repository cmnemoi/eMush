<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Game\DataFixtures\EventConfigFixtures;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
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
        $notMoveAction1Increase = new VariableEventModifierConfig();
        $notMoveAction1Increase
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([
                ActionEnum::MOVE => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::CONSUME => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::CONSUME_DRUG => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::SURGERY => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::SELF_SURGERY => ModifierRequirementEnum::NONE_TAGS,
                ActionTypeEnum::ACTION_ADMIN => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $notMoveAction1Increase->buildName();
        $manager->persist($notMoveAction1Increase);

        $notMoveAction2Increase = new VariableEventModifierConfig();
        $notMoveAction2Increase
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([
                ActionEnum::MOVE => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::CONSUME => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::CONSUME_DRUG => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::SURGERY => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::SELF_SURGERY => ModifierRequirementEnum::NONE_TAGS,
                ActionTypeEnum::ACTION_ADMIN => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $notMoveAction2Increase->buildName();
        $manager->persist($notMoveAction2Increase);

        $notMoveAction3Increase = new VariableEventModifierConfig();
        $notMoveAction3Increase
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(3)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([
                ActionEnum::MOVE => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::CONSUME => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::CONSUME_DRUG => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::SURGERY => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::SELF_SURGERY => ModifierRequirementEnum::NONE_TAGS,
                ActionTypeEnum::ACTION_ADMIN => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $notMoveAction3Increase->buildName();
        $manager->persist($notMoveAction3Increase);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::MAX_MOVEMENT_REDUCE_3);
        $reduceMax3MovementPoint = new DirectModifierConfig();
        $reduceMax3MovementPoint
            ->setTriggeredEvent($eventConfig)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setName('reduceMax3MovementPoint')
        ;
        $manager->persist($reduceMax3MovementPoint);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::MAX_MOVEMENT_REDUCE_5);
        $reduceMax5MovementPoint = new DirectModifierConfig();
        $reduceMax5MovementPoint
            ->setTriggeredEvent($eventConfig)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setName('reduceMax5MovementPoint')
        ;
        $manager->persist($reduceMax5MovementPoint);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::MAX_MOVEMENT_REDUCE_12);
        $reduceMax12MovementPoint = new DirectModifierConfig();
        $reduceMax12MovementPoint
            ->setTriggeredEvent($eventConfig)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setName('reduceMax12MovementPoint')
        ;
        $manager->persist($reduceMax12MovementPoint);

        $shootAction15PercentAccuracyLost = new VariableEventModifierConfig();
        $shootAction15PercentAccuracyLost
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(0.85)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setTagConstraints([ActionEnum::SHOOT => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $shootAction15PercentAccuracyLost->buildName();
        $manager->persist($shootAction15PercentAccuracyLost);

        $shootAction20PercentAccuracyLost = new VariableEventModifierConfig();
        $shootAction20PercentAccuracyLost
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(0.80)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setTagConstraints([ActionEnum::SHOOT => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $shootAction20PercentAccuracyLost->buildName();
        $manager->persist($shootAction20PercentAccuracyLost);

        $shootAction40PercentAccuracyLost = new VariableEventModifierConfig();
        $shootAction40PercentAccuracyLost
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(0.60)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setTagConstraints([ActionEnum::SHOOT => ModifierRequirementEnum::ANY_TAGS])
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
            EventConfigFixtures::class,
        ];
    }
}
