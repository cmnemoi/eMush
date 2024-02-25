<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\EventConfigFixtures;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\ModifierStrategyEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerCycleEvent;

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

        /** @var ModifierActivationRequirement $randActivationRequirement16 */
        $randActivationRequirement16 = $this->getReference(DiseaseModifierConfigFixtures::RANDOM_16);

        $randActivationRequirement70 = new ModifierActivationRequirement(ModifierRequirementEnum::RANDOM);
        $randActivationRequirement70
            ->setValue(70)
            ->buildName()
        ;
        $manager->persist($randActivationRequirement70);

        $catInRoomMove2MovementIncrease = new VariableEventModifierConfig('increaseMoveCost2MovementIfCatInRoom');
        $catInRoomMove2MovementIncrease
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTargetEvent(ActionEnum::MOVE)
            ->addModifierRequirement($catInRoomActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($catInRoomMove2MovementIncrease);

        $catInRoomNotMove2ActionIncrease = new VariableEventModifierConfig('increaseActionCost2ActionIfCatInRoom');
        $catInRoomNotMove2ActionIncrease
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([ActionEnum::MOVE => ModifierRequirementEnum::NONE_TAGS])
            ->addModifierRequirement($catInRoomActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($catInRoomNotMove2ActionIncrease);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::ACTION_REDUCE_1);
        $cycle1ActionLostRand16WithScreaming = new TriggerEventModifierConfig('screaming_for_player_set_-1actionPoint_on_new_cycle_if_random_16_test');
        $cycle1ActionLostRand16WithScreaming
            ->setTriggeredEvent($eventConfig)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->addModifierRequirement($randActivationRequirement16)
            ->setModifierName(ModifierNameEnum::SCREAMING)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($cycle1ActionLostRand16WithScreaming);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::HEALTH_REDUCE_1);
        $cycle1HealthLostRand16WithWallHeadBang = new TriggerEventModifierConfig('wall_head_bang_for_player_set_-1healthPoint_on_new_cycle_if_random_16_test');
        $cycle1HealthLostRand16WithWallHeadBang
            ->setTriggeredEvent($eventConfig)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->addModifierRequirement($randActivationRequirement16)
            ->setModifierName(ModifierNameEnum::WALL_HEAD_BANG)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($cycle1HealthLostRand16WithWallHeadBang);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::MORAL_REDUCE_1);
        $cycle1MoralLostRand70 = new TriggerEventModifierConfig('modifier_for_player_set_-1moralPoint_on_new_cycle_if_random_70_test');
        $cycle1MoralLostRand70
            ->setTriggeredEvent($eventConfig)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->addModifierRequirement($randActivationRequirement70)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($cycle1MoralLostRand70);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::MOVEMENT_REDUCE_2);
        $cycle2MovementLostRand16WithRunInCircles = new TriggerEventModifierConfig('run_in_circles_for_player_set_-2movementPoint_on_new_cycle_if_random_16_test');
        $cycle2MovementLostRand16WithRunInCircles
            ->setTriggeredEvent($eventConfig)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->addModifierRequirement($randActivationRequirement16)
            ->setModifierName(ModifierNameEnum::RUN_IN_CIRCLES)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($cycle2MovementLostRand16WithRunInCircles);

        $fourPeopleOneActionIncrease = new VariableEventModifierConfig('increaseActionCost1ActionIfMore4PeopleInRoom');
        $fourPeopleOneActionIncrease
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([
                ActionEnum::MOVE => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::CONVERT_ACTION_TO_MOVEMENT => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->addModifierRequirement($fourPeopleInRoomActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierStrategy(ModifierStrategyEnum::VARIABLE_MODIFIER)
        ;
        $manager->persist($fourPeopleOneActionIncrease);

        $fourPeopleOneMovementIncrease = new VariableEventModifierConfig('increaseMoveCost1MovementIfMore4PeopleInRoom');
        $fourPeopleOneMovementIncrease
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([ActionEnum::MOVE => ModifierRequirementEnum::ANY_TAGS])
            ->addModifierRequirement($fourPeopleInRoomActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierStrategy(ModifierStrategyEnum::VARIABLE_MODIFIER)
        ;
        $fourPeopleOneMovementIncrease->buildName();
        $manager->persist($fourPeopleOneMovementIncrease);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::MAX_ACTION_REDUCE_2);
        $reduceMax2ActionPoint = new DirectModifierConfig('reduceMaxAction2');
        $reduceMax2ActionPoint
            ->setTriggeredEvent($eventConfig)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($reduceMax2ActionPoint);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::MAX_MORAL_REDUCE_3);
        $reduceMax3MoralPoint = new DirectModifierConfig('reduceMaxMorale3');
        $reduceMax3MoralPoint
            ->setTriggeredEvent($eventConfig)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($reduceMax3MoralPoint);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::MAX_MORAL_REDUCE_4);
        $reduceMax4MoralPoint = new DirectModifierConfig('reduceMaxMorale4');
        $reduceMax4MoralPoint
            ->setTriggeredEvent($eventConfig)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
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
