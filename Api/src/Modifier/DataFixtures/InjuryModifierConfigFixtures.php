<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Event\PreparePercentageRollEvent;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;

use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierConditionEnum;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\ResourceMaxPointEvent;
use Mush\Player\Event\ResourcePointChangeEvent;

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
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $notMoveActionCondition = new ModifierCondition(ModifierConditionEnum::NOT_REASON);
        $notMoveActionCondition->setCondition(ActionEnum::MOVE);
        $manager->persist($notMoveActionCondition);

        $notMoveAction1Increase = new ModifierConfig(
            ModifierNameEnum::INJURY_COST_1_PA_ACTION_NOT_MOVE,
            ModifierReachEnum::PLAYER,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $notMoveAction1Increase
            ->excludeTargetEvent(ResourcePointChangeEvent::CHECK_CHANGE_ACTION_POINT, [ActionEnum::MOVE]);
        $manager->persist($notMoveAction1Increase);

        $notMoveAction2Increase = new ModifierConfig(
            ModifierNameEnum::INJURY_COST_2_PA_ACTION_NOT_MOVE,
            ModifierReachEnum::PLAYER,
            2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $notMoveAction2Increase
            ->excludeTargetEvent(ResourcePointChangeEvent::CHECK_CHANGE_ACTION_POINT, [ActionEnum::MOVE]);
        $manager->persist($notMoveAction2Increase);

        $notMoveAction3Increase = new ModifierConfig(
            ModifierNameEnum::INJURY_COST_3_PA_ACTION_NOT_MOVE,
            ModifierReachEnum::PLAYER,
            3,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $notMoveAction3Increase
            ->excludeTargetEvent(ResourcePointChangeEvent::CHECK_CHANGE_ACTION_POINT, [ActionEnum::MOVE]);

        $manager->persist($notMoveAction3Increase);

        $reduceMax3MovementPoint = new ModifierConfig(
            ModifierNameEnum::INJURY_LOSE_3_MAX_PM,
            ModifierReachEnum::PLAYER,
            -3,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $reduceMax3MovementPoint
            ->addTargetEvent(ResourceMaxPointEvent::CHECK_MAX_POINT);
        $manager->persist($reduceMax3MovementPoint);

        $reduceMax5MovementPoint = new ModifierConfig(
            ModifierNameEnum::INJURY_LOSE_5_MAX_PM,
            ModifierReachEnum::PLAYER,
            -5,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $reduceMax5MovementPoint
            ->addTargetEvent(ResourceMaxPointEvent::CHECK_MAX_POINT);
        $manager->persist($reduceMax5MovementPoint);

        $reduceMax12MovementPoint = new ModifierConfig(
            ModifierNameEnum::INJURY_LOSE_12_MAX_PM,
            ModifierReachEnum::PLAYER,
            -12,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $reduceMax12MovementPoint
            ->addTargetEvent(ResourceMaxPointEvent::CHECK_MAX_POINT);
        $manager->persist($reduceMax12MovementPoint);

        $shootAction15PercentAccuracyLost = new ModifierConfig(
            ModifierNameEnum::INJURY_LOSE_15_SHOOTING_ACCURACY,
            ModifierReachEnum::PLAYER,
            0.85,
            ModifierModeEnum::MULTIPLICATIVE
        );
        foreach (ActionTypeEnum::getShootActions() as $action) {
            $shootAction15PercentAccuracyLost
                ->addTargetEvent(PreparePercentageRollEvent::ACTION_ROLL_RATE, [$action]);
        }
        $manager->persist($shootAction15PercentAccuracyLost);

        $shootAction20PercentAccuracyLost = new ModifierConfig(
            ModifierNameEnum::INJURY_LOSE_20_SHOOTING_ACCURACY,
            ModifierReachEnum::PLAYER,
            0.80,
            ModifierModeEnum::MULTIPLICATIVE
        );
        foreach (ActionTypeEnum::getShootActions() as $action) {
            $shootAction20PercentAccuracyLost
                ->addTargetEvent(PreparePercentageRollEvent::ACTION_ROLL_RATE, [$action]);
        }
        $manager->persist($shootAction20PercentAccuracyLost);

        $shootAction40PercentAccuracyLost = new ModifierConfig(
            ModifierNameEnum::INJURY_LOSE_40_SHOOTING_ACCURACY,
            ModifierReachEnum::PLAYER,
            0.60,
            ModifierModeEnum::MULTIPLICATIVE
        );
        foreach (ActionTypeEnum::getShootActions() as $action) {
            $shootAction40PercentAccuracyLost
                ->addTargetEvent(PreparePercentageRollEvent::ACTION_ROLL_RATE, [$action]);
        }
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
        ];
    }
}
