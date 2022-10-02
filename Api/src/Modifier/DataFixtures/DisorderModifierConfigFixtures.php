<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Modifier\Entity\Condition\EquipmentInPlaceModifierCondition;
use Mush\Modifier\Entity\Condition\MinimumPlayerInPlaceModifierCondition;
use Mush\Modifier\Entity\Condition\RandomModifierCondition;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\ResourceMaxPointEvent;
use Mush\Player\Event\ResourcePointChangeEvent;

class DisorderModifierConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const CAT_IN_ROOM_MOVE_2_MOVEMENT_INCREASE = 'cat_in_room_move_2_movement_increase';
    public const CAT_IN_ROOM_NOT_MOVE_2_ACTION_INCREASE = 'cat_in_room_not_move_2_action_increase';
    public const CYCLE_1_ACTION_LOST_RAND_16_WITH_SCREAMING = 'cycle_1_action_lost_rand_16_with_screaming';
    public const CYCLE_1_HEALTH_LOST_RAND_16_WITH_WALL_HEAD_BANG = 'cycle_1_health_lost_rand_16_with_wall_head_bang';
    public const CYCLE_1_MORAL_LOST_RAND_70 = 'cycle_1_moral_lost_rand_70';
    public const CYCLE_2_MOVEMENT_LOST_RAND_16_WITH_RUN_IN_CIRCLES = 'cycle_2_movement_lost_rand_16_with_run_in_circles';
    public const FOUR_PEOPLE_ONE_ACTION_INCREASE = 'four_people_one_action_increase';
    public const FOUR_PEOPLE_ONE_MOVEMENT_INCREASE = 'four_people_one_movement_increase';
    public const REDUCE_MAX_2_ACTION_POINT = 'reduce_max_2_action_point';
    public const REDUCE_MAX_3_MORAL_POINT = 'reduce_max_3_moral_point';
    public const REDUCE_MAX_4_MORAL_POINT = 'reduce_max_4_moral_point';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $catInRoomCondition = new EquipmentInPlaceModifierCondition(ItemEnum::SCHRODINGER);
        $manager->persist($catInRoomCondition);

        $fourPeopleInRoomCondition = new MinimumPlayerInPlaceModifierCondition(4);
        $manager->persist($fourPeopleInRoomCondition);

        $randCondition16 = new RandomModifierCondition(16);
        $manager->persist($randCondition16);

        $randCondition70 = new RandomModifierCondition(70);
        $manager->persist($randCondition70);

        $catInRoomMove2MovementIncrease = new ModifierConfig(
            ModifierNameEnum::DISORDER_COST_2_MORE_MP_TO_MOVE_WHEN_CAT_IN_PLACE,
            ModifierReachEnum::PLAYER,
            2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $catInRoomMove2MovementIncrease
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CHANGE_MOVEMENT_POINT, [ActionEnum::MOVE])
            ->addCondition($catInRoomCondition);
        $manager->persist($catInRoomMove2MovementIncrease);

        $catInRoomNotMove2ActionIncrease = new ModifierConfig(
            ModifierNameEnum::DISORDER_COST_2_MORE_PA_ACTION_NOT_MOVE_WHEN_CAT_IN_PLACE,
            ModifierReachEnum::PLAYER,
            2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $catInRoomNotMove2ActionIncrease
            ->excludeTargetEvent(ResourcePointChangeEvent::CHECK_CHANGE_ACTION_POINT, [ActionEnum::MOVE])
            ->addCondition($catInRoomCondition);
        $manager->persist($catInRoomNotMove2ActionIncrease);

        $cycle1ActionLostRand16WithScreaming = new ModifierConfig(
            ModifierNameEnum::DISORDER_LOSE_1_PA_WITH_SCREAMING_RANDOM_16,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $cycle1ActionLostRand16WithScreaming
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE])
            ->setLogKeyWhenApplied(ModifierNameEnum::SCREAMING);
        $manager->persist($cycle1ActionLostRand16WithScreaming);

        $cycle1HealthLostRand16WithWallHeadBang = new ModifierConfig(
            ModifierNameEnum::DISORDER_LOSE_1_HP_WITH_WALL_BANG_RANDOM_16,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::HEALTH_POINT
        );
        $cycle1HealthLostRand16WithWallHeadBang
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE])
            ->addCondition($randCondition16)
            ->setLogKeyWhenApplied(ModifierNameEnum::WALL_HEAD_BANG);
        $manager->persist($cycle1HealthLostRand16WithWallHeadBang);

        $cycle1MoralLostRand70 = new ModifierConfig(
            ModifierNameEnum::DISORDER_LOSE_1_PMO_PER_CYCLE_RANDOM_70,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MORAL_POINT
        );
        $cycle1MoralLostRand70
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE])
            ->addCondition($randCondition70);
        $manager->persist($cycle1MoralLostRand70);

        $cycle2MovementLostRand16WithRunInCircles = new ModifierConfig(
            ModifierNameEnum::DISORDER_LOSE_2_PM_WITH_RUNNING_IN_CIRCLES_PER_CYCLE_RANDOM_16,
            ModifierReachEnum::PLAYER,
            -2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $cycle2MovementLostRand16WithRunInCircles
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE])
            ->addCondition($randCondition16)
            ->setLogKeyWhenApplied(ModifierNameEnum::RUN_IN_CIRCLES);
        $manager->persist($cycle2MovementLostRand16WithRunInCircles);

        $fourPeopleOneActionIncrease = new ModifierConfig(
            ModifierNameEnum::DISORDER_COST_1_PA_WHEN_FOUR_PEOPLE_OR_MORE,
            ModifierReachEnum::PLAYER,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $fourPeopleOneActionIncrease
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CHANGE_ACTION_POINT)
            ->addCondition($fourPeopleInRoomCondition);
        $manager->persist($fourPeopleOneActionIncrease);

        $fourPeopleOneMovementIncrease = new ModifierConfig(
            ModifierNameEnum::DISORDER_COST_1_PM_WHEN_FOUR_PEOPLE_OR_MORE,
            ModifierReachEnum::PLAYER,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $fourPeopleOneMovementIncrease
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CHANGE_MOVEMENT_POINT)
            ->addCondition($fourPeopleInRoomCondition);
        $manager->persist($fourPeopleOneMovementIncrease);

        $reduceMax2ActionPoint = new ModifierConfig(
            ModifierNameEnum::DISORDER_LOSE_2_MAX_PA,
            ModifierReachEnum::PLAYER,
            -2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $reduceMax2ActionPoint
            ->addTargetEvent(ResourceMaxPointEvent::CHECK_MAX_POINT);
        $manager->persist($reduceMax2ActionPoint);

        $reduceMax2MoralPoint = new ModifierConfig(
            ModifierNameEnum::DISORDER_LOSE_2_MAX_PMO,
            ModifierReachEnum::PLAYER,
            -2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MORAL_POINT
        );
        $reduceMax2MoralPoint
            ->addTargetEvent(ResourceMaxPointEvent::CHECK_MAX_POINT);
        $manager->persist($reduceMax2MoralPoint);

        $reduceMax3MoralPoint = new ModifierConfig(
            ModifierNameEnum::DISORDER_LOSE_3_MAX_PMO,
            ModifierReachEnum::PLAYER,
            -3,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MORAL_POINT
        );
        $reduceMax3MoralPoint
            ->addTargetEvent(ResourceMaxPointEvent::CHECK_MAX_POINT);
        $manager->persist($reduceMax3MoralPoint);

        $reduceMax4MoralPoint = new ModifierConfig(
            ModifierNameEnum::DISORDER_LOSE_4_MAX_PMO,
            ModifierReachEnum::PLAYER,
            -4,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MORAL_POINT
        );
        $reduceMax4MoralPoint
            ->addTargetEvent(ResourceMaxPointEvent::CHECK_MAX_POINT);
        $manager->persist($reduceMax4MoralPoint);

        $manager->flush();

        $this->addReference(self::CAT_IN_ROOM_MOVE_2_MOVEMENT_INCREASE, $catInRoomMove2MovementIncrease);
        $this->addReference(self::CAT_IN_ROOM_NOT_MOVE_2_ACTION_INCREASE, $catInRoomNotMove2ActionIncrease);
        $this->addReference(self::CYCLE_1_ACTION_LOST_RAND_16_WITH_SCREAMING, $cycle1ActionLostRand16WithScreaming);
        $this->addReference(self::CYCLE_1_HEALTH_LOST_RAND_16_WITH_WALL_HEAD_BANG, $cycle1HealthLostRand16WithWallHeadBang);
        $this->addReference(self::CYCLE_1_MORAL_LOST_RAND_70, $cycle1MoralLostRand70);
        $this->addReference(self::CYCLE_2_MOVEMENT_LOST_RAND_16_WITH_RUN_IN_CIRCLES, $cycle2MovementLostRand16WithRunInCircles);
        $this->addReference(self::FOUR_PEOPLE_ONE_ACTION_INCREASE, $fourPeopleOneActionIncrease);
        $this->addReference(self::FOUR_PEOPLE_ONE_MOVEMENT_INCREASE, $fourPeopleOneMovementIncrease);
        $this->addReference(self::REDUCE_MAX_2_ACTION_POINT, $reduceMax2ActionPoint);
        $this->addReference(self::REDUCE_MAX_3_MORAL_POINT, $reduceMax3MoralPoint);
        $this->addReference(self::REDUCE_MAX_4_MORAL_POINT, $reduceMax4MoralPoint);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
