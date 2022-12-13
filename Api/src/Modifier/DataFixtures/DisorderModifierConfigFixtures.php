<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
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
use Mush\Player\Enum\PlayerVariableEnum;

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
    public const NOT_REASON_MOVE = 'not_reason_move';

    public function load(ObjectManager $manager): void
    {
        $catInRoomCondition = new ModifierCondition(ModifierConditionEnum::ITEM_IN_ROOM);
        $catInRoomCondition
            ->setCondition(ItemEnum::SCHRODINGER)
            ->buildName()
        ;
        $manager->persist($catInRoomCondition);

        $fourPeopleInRoomCondition = new ModifierCondition(ModifierConditionEnum::PLAYER_IN_ROOM);
        $fourPeopleInRoomCondition
            ->setCondition(ModifierConditionEnum::FOUR_PEOPLE)
            ->buildName()
        ;
        $manager->persist($fourPeopleInRoomCondition);

        $notMoveActionCondition = new ModifierCondition(ModifierConditionEnum::NOT_REASON);
        $notMoveActionCondition
            ->setCondition(ActionEnum::MOVE)
            ->buildName()
        ;
        $manager->persist($notMoveActionCondition);

        /** @var ModifierCondition $randCondition16 */
        $randCondition16 = $this->getReference(DiseaseModifierConfigFixtures::RANDOM_16);

        $randCondition70 = new ModifierCondition(ModifierConditionEnum::RANDOM);
        $randCondition70
            ->setValue(70)
            ->buildName()
        ;
        $manager->persist($randCondition70);

        $catInRoomMove2MovementIncrease = new ModifierConfig();
        $catInRoomMove2MovementIncrease
            ->setScope(ActionEnum::MOVE)
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(2)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->addModifierCondition($catInRoomCondition)
            ->buildName()
        ;
        $manager->persist($catInRoomMove2MovementIncrease);

        $catInRoomNotMove2ActionIncrease = new ModifierConfig();
        $catInRoomNotMove2ActionIncrease
            ->setScope(ModifierScopeEnum::ACTIONS)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(2)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->addModifierCondition($catInRoomCondition)
            ->addModifierCondition($notMoveActionCondition)
            ->buildName()
        ;
        $manager->persist($catInRoomNotMove2ActionIncrease);

        $cycle1ActionLostRand16WithScreaming = new ModifierConfig();
        $cycle1ActionLostRand16WithScreaming
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($randCondition16)
            ->setModifierName(ModifierNameEnum::SCREAMING)
            ->buildName()
        ;
        $manager->persist($cycle1ActionLostRand16WithScreaming);

        $cycle1HealthLostRand16WithWallHeadBang = new ModifierConfig();
        $cycle1HealthLostRand16WithWallHeadBang
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($randCondition16)
            ->setModifierName(ModifierNameEnum::WALL_HEAD_BANG)
            ->buildName()
        ;
        $manager->persist($cycle1HealthLostRand16WithWallHeadBang);

        $cycle1MoralLostRand70 = new ModifierConfig();
        $cycle1MoralLostRand70
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($randCondition70)
            ->buildName()
        ;
        $manager->persist($cycle1MoralLostRand70);

        $cycle2MovementLostRand16WithRunInCircles = new ModifierConfig();
        $cycle2MovementLostRand16WithRunInCircles
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-2)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($randCondition16)
            ->setModifierName(ModifierNameEnum::RUN_IN_CIRCLES)
            ->buildName()
        ;
        $manager->persist($cycle2MovementLostRand16WithRunInCircles);

        $fourPeopleOneActionIncrease = new ModifierConfig();
        $fourPeopleOneActionIncrease
            ->setScope(ModifierScopeEnum::ACTIONS)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->addModifierCondition($fourPeopleInRoomCondition)
            ->buildName()
        ;
        $manager->persist($fourPeopleOneActionIncrease);

        $fourPeopleOneMovementIncrease = new ModifierConfig();
        $fourPeopleOneMovementIncrease
            ->setScope(ModifierScopeEnum::ACTIONS)
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->addModifierCondition($fourPeopleInRoomCondition)
            ->buildName()
        ;
        $manager->persist($fourPeopleOneMovementIncrease);

        $reduceMax2ActionPoint = new ModifierConfig();
        $reduceMax2ActionPoint
            ->setScope(ModifierScopeEnum::MAX_POINT)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-2)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->buildName()
        ;
        $manager->persist($reduceMax2ActionPoint);

        $reduceMax3MoralPoint = new ModifierConfig();
        $reduceMax3MoralPoint
            ->setScope(ModifierScopeEnum::MAX_POINT)
            ->setTarget(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-3)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->buildName()
        ;
        $manager->persist($reduceMax3MoralPoint);

        $reduceMax4MoralPoint = new ModifierConfig();
        $reduceMax4MoralPoint
            ->setScope(ModifierScopeEnum::MAX_POINT)
            ->setTarget(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-4)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->buildName()
        ;
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
        $this->addReference(self::NOT_REASON_MOVE, $notMoveActionCondition);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            DiseaseModifierConfigFixtures::class,
        ];
    }
}
