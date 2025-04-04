<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Chat\Enum\MessageModificationEnum;
use Mush\Chat\Event\MessageEvent;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\EventConfigFixtures;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\ModifierStrategyEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;

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
    public const DEAF_LISTEN_MODIFIER = 'deaf_listen_modifier';
    public const DEAF_SPEAK_MODIFIER = 'deaf_speak_modifier';
    public const COPROLALIA_MODIFIER = 'coprolalia_modifier';
    public const PARANOIA_MODIFIER = 'paranoia_modifier';
    public const PARANOIA_DENIAL_MODIFIER = 'paranoia_denial_modifier';
    public const CANNOT_MOVE = 'cannot_move';
    public const PREVENT_PICK_HEAVY_ITEMS = 'prevent_pick_heavy';
    public const PREVENT_ATTACK_ACTION = 'prevent_attack_action';
    public const PREVENT_PILOTING = 'prevent_piloting';
    public const PREVENT_SHOOT_ACTION = 'prevent_shoot_action';
    public const MUTE_MODIFIER = 'mute_modifier';
    public const PREVENT_SPOKEN = 'prevent_spoken';
    public const SEPTICEMIA_ON_CYCLE_CHANGE = 'septicemia_on_cycle_change';
    public const SEPTICEMIA_ON_DIRTY_EVENT = 'septicemia_on_dirty_event';
    public const SEPTICEMIA_ON_POST_ACTION = 'septicemia_on_post_action';
    public const FEAR_OF_CATS = 'fear_of_cats';
    public const PSYCHOTIC_ATTACKS = 'psychotic_attacks';

    public const BITING = 'biting';
    public const BREAKOUTS = 'breakouts';
    public const CAT_SNEEZING = 'cat_sneezing';
    public const CAT_ALLERGY_SYMPTOM = 'cat_allergy_symptom';
    public const CONSUME_VOMITING = 'consume_vomiting';
    public const CYCLE_DIRTINESS = 'cycle_dirtiness';
    public const CYCLE_DIRTINESS_RAND_40 = 'cycle_dirtiness_rand_40';
    public const DROOLING = 'drooling';
    public const FOAMING_MOUTH = 'foaming_mouth';
    public const MOVE_VOMITING = 'move_vomiting';
    public const MUSH_SNEEZING = 'mush_sneezing';

    public function load(ObjectManager $manager): void
    {
        $notMoveAction1Increase = new VariableEventModifierConfig('increaseActionCost1Action');
        $notMoveAction1Increase
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([
                ActionEnum::MOVE->value => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::CONSUME->value => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::CONSUME_DRUG->value => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::SURGERY->value => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::SELF_SURGERY->value => ModifierRequirementEnum::NONE_TAGS,
                ActionTypeEnum::ACTION_ADMIN->value => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $manager->persist($notMoveAction1Increase);

        $notMoveAction2Increase = new VariableEventModifierConfig('increaseActionCost2Action');
        $notMoveAction2Increase
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([
                ActionEnum::MOVE->value => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::CONSUME->value => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::CONSUME_DRUG->value => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::SURGERY->value => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::SELF_SURGERY->value => ModifierRequirementEnum::NONE_TAGS,
                ActionTypeEnum::ACTION_ADMIN->value => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $manager->persist($notMoveAction2Increase);

        $notMoveAction3Increase = new VariableEventModifierConfig('increaseActionCost3Action');
        $notMoveAction3Increase
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(3)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([
                ActionEnum::MOVE->value => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::CONSUME->value => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::CONSUME_DRUG->value => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::SURGERY->value => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::SELF_SURGERY->value => ModifierRequirementEnum::NONE_TAGS,
                ActionTypeEnum::ACTION_ADMIN->value => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $manager->persist($notMoveAction3Increase);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::MAX_MOVEMENT_REDUCE_3);
        $reduceMax3MovementPoint = new DirectModifierConfig('reduceMax3MovementPoint');
        $reduceMax3MovementPoint
            ->setTriggeredEvent($eventConfig)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $manager->persist($reduceMax3MovementPoint);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::MAX_MOVEMENT_REDUCE_5);
        $reduceMax5MovementPoint = new DirectModifierConfig('reduceMax5MovementPoint');
        $reduceMax5MovementPoint
            ->setTriggeredEvent($eventConfig)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $manager->persist($reduceMax5MovementPoint);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::MAX_MOVEMENT_REDUCE_12);
        $reduceMax12MovementPoint = new DirectModifierConfig('reduceMax12MovementPoint');
        $reduceMax12MovementPoint
            ->setTriggeredEvent($eventConfig)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $manager->persist($reduceMax12MovementPoint);

        $shootAction15PercentAccuracyLost = new VariableEventModifierConfig('reduceShootPercentage15Percents');
        $shootAction15PercentAccuracyLost
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(0.85)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setTagConstraints([ActionEnum::SHOOT->value => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $manager->persist($shootAction15PercentAccuracyLost);

        $shootAction20PercentAccuracyLost = new VariableEventModifierConfig('reduceShootPercentage20Percents');
        $shootAction20PercentAccuracyLost
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(0.80)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setTagConstraints([ActionEnum::SHOOT->value => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $shootAction20PercentAccuracyLost->buildName();
        $manager->persist($shootAction20PercentAccuracyLost);

        $shootAction40PercentAccuracyLost = new VariableEventModifierConfig('reduceShootPercentage40Percents');
        $shootAction40PercentAccuracyLost
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(0.60)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setTagConstraints([ActionEnum::SHOOT->value => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $shootAction40PercentAccuracyLost->buildName();
        $manager->persist($shootAction40PercentAccuracyLost);

        $deafSpeak = new EventModifierConfig(MessageModificationEnum::DEAF_SPEAK);
        $deafSpeak
            ->setTargetEvent(MessageEvent::NEW_MESSAGE)
            ->setPriority(ModifierPriorityEnum::OVERRIDE_VALUE_PRIORITY)
            ->setModifierStrategy(ModifierStrategyEnum::MESSAGE_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setName('deaf_speak_modifier_fixture');
        $manager->persist($deafSpeak);

        $deafListen = new EventModifierConfig(MessageModificationEnum::DEAF_LISTEN);
        $deafListen
            ->setTargetEvent(MessageEvent::READ_MESSAGE)
            ->setPriority(ModifierPriorityEnum::OVERRIDE_VALUE_PRIORITY)
            ->setModifierStrategy(ModifierStrategyEnum::PREVENT_EVENT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setName('deaf_listen__modifier_fixture');
        $manager->persist($deafListen);

        $paranoia = new EventModifierConfig(MessageModificationEnum::PARANOIA_MESSAGES);
        $paranoia
            ->setTargetEvent(MessageEvent::NEW_MESSAGE)
            ->setPriority(ModifierPriorityEnum::OVERRIDE_VALUE_PRIORITY)
            ->setModifierStrategy(ModifierStrategyEnum::MESSAGE_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $manager->persist($paranoia);

        $paranoiaDenial = new EventModifierConfig(MessageModificationEnum::PARANOIA_DENIAL);
        $paranoiaDenial
            ->setTargetEvent(MessageEvent::READ_MESSAGE)
            ->setPriority(ModifierPriorityEnum::OVERRIDE_VALUE_PRIORITY)
            ->setModifierStrategy(ModifierStrategyEnum::MESSAGE_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $manager->persist($paranoiaDenial);

        $coprolalia = new EventModifierConfig(MessageModificationEnum::COPROLALIA_MESSAGES);
        $coprolalia
            ->setTargetEvent(MessageEvent::NEW_MESSAGE)
            ->setPriority(ModifierPriorityEnum::OVERRIDE_VALUE_PRIORITY)
            ->setModifierStrategy(ModifierStrategyEnum::MESSAGE_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setName('coprolalia_modifier_fixture');
        $manager->persist($coprolalia);

        $cannotMoveModifier = EventModifierConfig::fromConfigData(
            ModifierConfigData::getByName('prevent_move')
        );
        $manager->persist($cannotMoveModifier);

        $preventTakeHeavy = new EventModifierConfig('prevent_pick_heavy_item');
        $preventTakeHeavy
            ->setTargetEvent(ActionEvent::PRE_ACTION)
            ->setApplyWhenTargeted(false)
            ->setTagConstraints([
                ActionEnum::TAKE->value => ModifierRequirementEnum::ALL_TAGS,
                EquipmentStatusEnum::HEAVY => ModifierRequirementEnum::ALL_TAGS,
            ])
            ->setPriority(ModifierPriorityEnum::PREVENT_EVENT)
            ->setModifierStrategy(ModifierStrategyEnum::PREVENT_EVENT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $manager->persist($preventTakeHeavy);

        $preventAttackActions = EventModifierConfig::fromConfigData(
            ModifierConfigData::getByName('prevent_attack_actions')
        );
        $manager->persist($preventAttackActions);

        $preventPiloting = EventModifierConfig::fromConfigData(
            ModifierConfigData::getByName('prevent_piloting_actions')
        );
        $manager->persist($preventPiloting);

        $preventShoot = EventModifierConfig::fromConfigData(
            ModifierConfigData::getByName('prevent_shoot_actions')
        );
        $manager->persist($preventShoot);

        $muteModifier = EventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::MUTE_PREVENT_MESSAGES_MODIFIER)
        );
        $manager->persist($muteModifier);

        $cannotSpeakModifier = EventModifierConfig::fromConfigData(
            ModifierConfigData::getByName('prevent_spoken_actions')
        );
        $manager->persist($cannotSpeakModifier);

        $dirtyRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::HOLDER_HAS_STATUS);
        $dirtyRequirement
            ->setValue(100)
            ->setName('player_dirty_status_requirement_fixtures')
            ->setActivationRequirement(PlayerStatusEnum::DIRTY);
        $manager->persist($dirtyRequirement);
        $septicemiaCycleChange = new EventModifierConfig('septicemia_cycle_change');
        $septicemiaCycleChange
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setApplyWhenTargeted(true)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->addModifierRequirement($dirtyRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(SymptomEnum::SEPTICEMIA);
        $manager->persist($septicemiaCycleChange);

        $septicemiaPostAction = new EventModifierConfig('septicemia_post_action');
        $septicemiaPostAction
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setApplyWhenTargeted(false)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->addModifierRequirement($dirtyRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(SymptomEnum::SEPTICEMIA);
        $manager->persist($septicemiaPostAction);

        $septicemiaOnDirty = new EventModifierConfig('septicemia_on_dirty');
        $septicemiaOnDirty
            ->setTargetEvent(StatusEvent::STATUS_APPLIED)
            ->setApplyWhenTargeted(true)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setTagConstraints([PlayerStatusEnum::DIRTY => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(SymptomEnum::SEPTICEMIA);
        $manager->persist($septicemiaOnDirty);

        $catRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::ITEM_IN_ROOM);
        $catRequirement
            ->setValue(100)
            ->setName('cat_in_room_requirement_fixtures')
            ->setActivationRequirement(ItemEnum::SCHRODINGER);
        $manager->persist($catRequirement);

        /** @var ModifierActivationRequirement $random50 */
        $random50 = $this->getReference(DiseaseModifierConfigFixtures::RANDOM_50);
        $fearOfCat = new EventModifierConfig('fear_of_cat_modifier');
        $fearOfCat
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setApplyWhenTargeted(false)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setTagConstraints([ActionEnum::MOVE->value => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierActivationRequirements([$random50, $catRequirement])
            ->setModifierName(SymptomEnum::FEAR_OF_CATS);
        $manager->persist($fearOfCat);

        /** @var ModifierActivationRequirement $random16 */
        $random16 = $this->getReference(DiseaseModifierConfigFixtures::RANDOM_16);
        $psychoticAttack = new EventModifierConfig('psychotic_attacks');
        $psychoticAttack
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setApplyWhenTargeted(false)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setTagConstraints([ActionEnum::MOVE->value => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierActivationRequirements([$random16])
            ->setModifierName(SymptomEnum::PSYCHOTIC_ATTACKS);
        $manager->persist($psychoticAttack);

        $bitingModifier = new EventModifierConfig('biting_modifier');
        $bitingModifier
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setApplyWhenTargeted(true)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierActivationRequirements([])
            ->setModifierName(SymptomEnum::BITING);
        $manager->persist($bitingModifier);

        $breakoutsModifier = new EventModifierConfig('breakouts_modifier');
        $breakoutsModifier
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setApplyWhenTargeted(false)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setTagConstraints([ActionEnum::MOVE->value => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierActivationRequirements([$random16])
            ->setModifierName(SymptomEnum::BREAKOUTS);
        $manager->persist($breakoutsModifier);

        $catAllergySymptom = new EventModifierConfig('cat_allergy');
        $catAllergySymptom
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setApplyWhenTargeted(false)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setTagConstraints([
                ItemEnum::SCHRODINGER . '_action_target' => ModifierRequirementEnum::ALL_TAGS,
                ActionEnum::TAKE_CAT->value => ModifierRequirementEnum::ALL_TAGS,
            ])
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setModifierActivationRequirements([])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(SymptomEnum::CAT_ALLERGY);
        $manager->persist($catAllergySymptom);

        $catSneezing = new EventModifierConfig('cat_sneezing');
        $catSneezing
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setApplyWhenTargeted(false)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setTagConstraints([
                ActionEnum::MOVE->value => ModifierRequirementEnum::ALL_TAGS,
            ])
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setModifierActivationRequirements([
                $random16,
                $catRequirement,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(SymptomEnum::SNEEZING);
        $manager->persist($catSneezing);

        $vomitingConsume = new EventModifierConfig('vomiting_consume');
        $vomitingConsume
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setApplyWhenTargeted(false)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setTagConstraints([
                ActionEnum::CONSUME_DRUG->value => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::CONSUME->value => ModifierRequirementEnum::ANY_TAGS,
                SymptomEnum::VOMITING => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(SymptomEnum::VOMITING);
        $manager->persist($vomitingConsume);

        /** @var ModifierActivationRequirement $random40 */
        $random40 = $this->getReference(DiseaseModifierConfigFixtures::RANDOM_40);
        $vomitingMove = new EventModifierConfig('vomiting_move_random_40');
        $vomitingMove
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setApplyWhenTargeted(false)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setTagConstraints([
                ActionEnum::MOVE->value => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->addModifierRequirement($random40)
            ->setModifierName(SymptomEnum::VOMITING);
        $manager->persist($vomitingMove);

        $cycleDirtiness = new EventModifierConfig('cycle_dirtiness');
        $cycleDirtiness
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setApplyWhenTargeted(true)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(SymptomEnum::DIRTINESS);
        $manager->persist($cycleDirtiness);

        $cycleDirtinessRandom = new EventModifierConfig('cycle_dirtiness_random_40');
        $cycleDirtinessRandom
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setApplyWhenTargeted(true)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->addModifierRequirement($random40)
            ->setModifierName(SymptomEnum::DIRTINESS);
        $manager->persist($cycleDirtinessRandom);

        $drooling = new EventModifierConfig('drooling_on_move');
        $drooling
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setApplyWhenTargeted(false)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setTagConstraints([
                ActionEnum::MOVE->value => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->addModifierRequirement($random16)
            ->setModifierName(SymptomEnum::DROOLING);
        $manager->persist($drooling);

        $foamingMouth = new EventModifierConfig('foaming_mouth');
        $foamingMouth
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setApplyWhenTargeted(false)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setTagConstraints([
                ActionEnum::MOVE->value => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->addModifierRequirement($random16)
            ->setModifierName(SymptomEnum::FOAMING_MOUTH);
        $manager->persist($foamingMouth);

        $mushInRoomRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::PLAYER_IN_ROOM);
        $mushInRoomRequirement
            ->setActivationRequirement(ModifierRequirementEnum::MUSH_IN_ROOM)
            ->setName('mush_in_room_requirement_fixture');
        $manager->persist($mushInRoomRequirement);
        $mushSneezing = new EventModifierConfig('mush_sneezing');
        $mushSneezing
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setApplyWhenTargeted(false)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setTagConstraints([
                ActionEnum::MOVE->value => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierActivationRequirements([
                $random16,
                $mushInRoomRequirement,
            ])
            ->setModifierName(SymptomEnum::SNEEZING);
        $manager->persist($mushSneezing);

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
        $this->addReference(self::DEAF_SPEAK_MODIFIER, $deafSpeak);
        $this->addReference(self::DEAF_LISTEN_MODIFIER, $deafListen);
        $this->addReference(self::COPROLALIA_MODIFIER, $coprolalia);
        $this->addReference(self::PARANOIA_MODIFIER, $paranoia);
        $this->addReference(self::PARANOIA_DENIAL_MODIFIER, $paranoiaDenial);
        $this->addReference(self::CANNOT_MOVE, $cannotMoveModifier);
        $this->addReference(self::PREVENT_PICK_HEAVY_ITEMS, $preventTakeHeavy);
        $this->addReference(self::PREVENT_ATTACK_ACTION, $preventAttackActions);
        $this->addReference(self::PREVENT_PILOTING, $preventPiloting);
        $this->addReference(self::PREVENT_SHOOT_ACTION, $preventShoot);
        $this->addReference(self::MUTE_MODIFIER, $muteModifier);
        $this->addReference(self::PREVENT_SPOKEN, $cannotSpeakModifier);
        $this->addReference(self::SEPTICEMIA_ON_CYCLE_CHANGE, $septicemiaCycleChange);
        $this->addReference(self::SEPTICEMIA_ON_DIRTY_EVENT, $septicemiaOnDirty);
        $this->addReference(self::SEPTICEMIA_ON_POST_ACTION, $septicemiaPostAction);
        $this->addReference(self::FEAR_OF_CATS, $fearOfCat);
        $this->addReference(self::PSYCHOTIC_ATTACKS, $psychoticAttack);
        $this->addReference(self::BITING, $bitingModifier);
        $this->addReference(self::BREAKOUTS, $breakoutsModifier);
        $this->addReference(self::CAT_ALLERGY_SYMPTOM, $catAllergySymptom);
        $this->addReference(self::CAT_SNEEZING, $catSneezing);
        $this->addReference(self::CONSUME_VOMITING, $vomitingConsume);
        $this->addReference(self::CYCLE_DIRTINESS, $cycleDirtiness);
        $this->addReference(self::CYCLE_DIRTINESS_RAND_40, $cycleDirtinessRandom);
        $this->addReference(self::DROOLING, $drooling);
        $this->addReference(self::FOAMING_MOUTH, $foamingMouth);
        $this->addReference(self::MOVE_VOMITING, $vomitingMove);
        $this->addReference(self::MUSH_SNEEZING, $mushSneezing);
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
