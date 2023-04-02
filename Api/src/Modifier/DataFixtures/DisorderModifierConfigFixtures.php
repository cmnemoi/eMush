<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\EventConfigFixtures;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Enum\EventEnum;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
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
        $catInRoomActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::ITEM_IN_ROOM);
        $catInRoomActivationRequirement
            ->setActivationRequirement(ItemEnum::SCHRODINGER)
            ->buildName()
        ;
        $manager->persist($catInRoomActivationRequirement);

        $fourPeopleInRoomActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::PLAYER_IN_ROOM);
        $fourPeopleInRoomActivationRequirement
            ->setActivationRequirement(ModifierRequirementEnum::FOUR_PEOPLE)
            ->buildName()
        ;
        $manager->persist($fourPeopleInRoomActivationRequirement);

        $notMoveActionActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::NOT_REASON);
        $notMoveActionActivationRequirement
            ->setActivationRequirement(ActionEnum::MOVE)
            ->buildName()
        ;
        $manager->persist($notMoveActionActivationRequirement);

        /** @var ModifierActivationRequirement $randActivationRequirement16 */
        $randActivationRequirement16 = $this->getReference(DiseaseModifierConfigFixtures::RANDOM_16);

        $randActivationRequirement70 = new ModifierActivationRequirement(ModifierRequirementEnum::RANDOM);
        $randActivationRequirement70
            ->setValue(70)
            ->buildName()
        ;
        $manager->persist($randActivationRequirement70);

        $catInRoomMove2MovementIncrease = new VariableEventModifierConfig();
        $catInRoomMove2MovementIncrease
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionEnum::MOVE)
            ->addModifierRequirement($catInRoomActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $catInRoomMove2MovementIncrease->buildName();
        $manager->persist($catInRoomMove2MovementIncrease);

        $catInRoomNotMove2ActionIncrease = new VariableEventModifierConfig();
        $catInRoomNotMove2ActionIncrease
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::ACTIONS)
            ->addModifierRequirement($catInRoomActivationRequirement)
            ->addModifierRequirement($notMoveActionActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $catInRoomNotMove2ActionIncrease->buildName();
        $manager->persist($catInRoomNotMove2ActionIncrease);

        $cycle1ActionLostRand16WithScreaming = new VariableEventModifierConfig();
        $cycle1ActionLostRand16WithScreaming
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->addModifierRequirement($randActivationRequirement16)
            ->setModifierName(ModifierNameEnum::SCREAMING)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $cycle1ActionLostRand16WithScreaming->buildName();
        $manager->persist($cycle1ActionLostRand16WithScreaming);

        $cycle1HealthLostRand16WithWallHeadBang = new VariableEventModifierConfig();
        $cycle1HealthLostRand16WithWallHeadBang
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->addModifierRequirement($randActivationRequirement16)
            ->setModifierName(ModifierNameEnum::WALL_HEAD_BANG)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $cycle1HealthLostRand16WithWallHeadBang->buildName();
        $manager->persist($cycle1HealthLostRand16WithWallHeadBang);

        $cycle1MoralLostRand70 = new VariableEventModifierConfig();
        $cycle1MoralLostRand70
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->addModifierRequirement($randActivationRequirement70)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $cycle1MoralLostRand70->buildName();
        $manager->persist($cycle1MoralLostRand70);

        $cycle2MovementLostRand16WithRunInCircles = new VariableEventModifierConfig();
        $cycle2MovementLostRand16WithRunInCircles
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-2)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->addModifierRequirement($randActivationRequirement16)
            ->setModifierName(ModifierNameEnum::RUN_IN_CIRCLES)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $cycle2MovementLostRand16WithRunInCircles->buildName();
        $manager->persist($cycle2MovementLostRand16WithRunInCircles);

        $fourPeopleOneActionIncrease = new VariableEventModifierConfig();
        $fourPeopleOneActionIncrease
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::ACTIONS)
            ->addModifierRequirement($fourPeopleInRoomActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $fourPeopleOneActionIncrease->buildName();
        $manager->persist($fourPeopleOneActionIncrease);

        $fourPeopleOneMovementIncrease = new VariableEventModifierConfig();
        $fourPeopleOneMovementIncrease
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::ACTIONS)
            ->addModifierRequirement($fourPeopleInRoomActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $fourPeopleOneMovementIncrease->buildName();
        $manager->persist($fourPeopleOneMovementIncrease);

        $reduceMax2ActionPoint = new VariableEventModifierConfig();
        $reduceMax2ActionPoint
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::MAX_POINT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $reduceMax2ActionPoint->buildName();
        $manager->persist($reduceMax2ActionPoint);

        $reduceMax3MoralPoint = new VariableEventModifierConfig();
        $reduceMax3MoralPoint
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-3)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::MAX_POINT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $reduceMax3MoralPoint->buildName();
        $manager->persist($reduceMax3MoralPoint);

        $reduceMax4MoralPoint = new VariableEventModifierConfig();
        $reduceMax4MoralPoint
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-4)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::MAX_POINT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $reduceMax4MoralPoint->buildName();
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
        $this->addReference(self::NOT_REASON_MOVE, $notMoveActionActivationRequirement);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            EventConfigFixtures::class,
            DiseaseModifierConfigFixtures::class,
        ];
    }
}
