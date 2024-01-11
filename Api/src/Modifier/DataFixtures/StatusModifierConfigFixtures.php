<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Game\DataFixtures\EventConfigFixtures;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Game\Event\RollPercentageEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Config\EventModifierConfig;
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

class StatusModifierConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const FROZEN_MODIFIER = 'frozen_modifier';
    public const DISABLED_CONVERSION_MODIFIER = 'disabled_conversion_modifier';
    public const DISABLED_NOT_ALONE_MODIFIER = 'disabled_not_alone_modifier';
    public const PACIFIST_MODIFIER = 'pacifist_modifier';
    public const BURDENED_MODIFIER = 'burdened_modifier';
    public const ANTISOCIAL_MODIFIER = 'antisocial_modifier';
    public const LOST_MODIFIER = 'lost_modifier';
    public const LYING_DOWN_MODIFIER = 'lying_down_modifier';
    public const STARVING_MODIFIER = 'starving_modifier';
    public const INCREASE_CYCLE_DISEASE_CHANCES_30 = 'increase_cycle_disease_chances_30';

    public const MUSH_SHOWER_MODIFIER = 'mush_shower_modifier';
    public const MUSH_CONSUME_SATIETY_MODIFIER = 'mush_consume_satiety_modifier';
    public const MUSH_CONSUME_MODIFIER = 'mush_consume_modifier';

    public function load(ObjectManager $manager): void
    {
        $frozenModifier = new VariableEventModifierConfig('frozenIncreaseConsumeCost1Action');
        $frozenModifier
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([ActionEnum::CONSUME => ModifierRequirementEnum::ANY_TAGS])
            ->setApplyOnTarget(true)
            ->setModifierRange(ModifierHolderClassEnum::EQUIPMENT)
        ;
        $manager->persist($frozenModifier);

        $disabledConversionModifier = new VariableEventModifierConfig('disabledConversionModifier');
        $disabledConversionModifier
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([ActionEnum::CONVERT_ACTION_TO_MOVEMENT => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($disabledConversionModifier);

        $notAloneActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::PLAYER_IN_ROOM);
        $notAloneActivationRequirement
            ->setActivationRequirement(ModifierRequirementEnum::NOT_ALONE)
            ->buildName()
        ;
        $manager->persist($notAloneActivationRequirement);

        $disabledNotAloneModifier = new VariableEventModifierConfig('disabledDecreaseMoveCost1MovementIfNotAlone');
        $disabledNotAloneModifier
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([ActionEnum::MOVE => ModifierRequirementEnum::ALL_TAGS])
            ->addModifierRequirement($notAloneActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $disabledNotAloneModifier->buildName();
        $manager->persist($disabledNotAloneModifier);

        $pacifistModifier = new VariableEventModifierConfig('pacifistIncreaseAggressiveActionCost2Action');
        $pacifistModifier
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([ActionTypeEnum::ACTION_AGGRESSIVE => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLACE)
        ;
        $manager->persist($pacifistModifier);

        $burdenedModifier = new VariableEventModifierConfig('burdenedIncreaseMoveCost1Action');
        $burdenedModifier
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([ActionEnum::MOVE => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $burdenedModifier->buildName();
        $manager->persist($burdenedModifier);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::MORAL_REDUCE_1);
        $antisocialModifier = new TriggerEventModifierConfig(ModifierNameEnum::ANTISOCIAL_MODIFIER);
        $antisocialModifier
            ->setTriggeredEvent($eventConfig)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setApplyOnTarget(true)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->addModifierRequirement($notAloneActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(ModifierNameEnum::ANTISOCIAL_MODIFIER)
        ;
        $manager->persist($antisocialModifier);

        $lostModifier = new TriggerEventModifierConfig('lostModifier');
        $lostModifier
            ->setTriggeredEvent($eventConfig)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setApplyOnTarget(true)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($lostModifier);

        $lyingDownModifier = new VariableEventModifierConfig(ModifierNameEnum::LYING_DOWN_MODIFIER);
        $lyingDownModifier
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setApplyOnTarget(true)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTagConstraints(['base_player_cycle_change' => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $lyingDownModifier->buildName();
        $manager->persist($lyingDownModifier);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::HEALTH_REDUCE_1);
        $starvingModifier = new TriggerEventModifierConfig('starvingModifier');
        $starvingModifier
            ->setTriggeredEvent($eventConfig)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setApplyOnTarget(true)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(ModifierNameEnum::STARVING)
        ;
        $manager->persist($starvingModifier);

        $increaseCycleDiseaseChances30 = new VariableEventModifierConfig('increaseCycleDiseaseChances30Percents');
        $increaseCycleDiseaseChances30
            ->setTargetVariable(RollPercentageEvent::ROLL_PERCENTAGE)
            ->setDelta(30)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setApplyOnTarget(false)
            ->setTagConstraints([PlayerEvent::CYCLE_DISEASE => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($increaseCycleDiseaseChances30);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::HEALTH_REDUCE_3);
        $mushShowerModifier = new TriggerEventModifierConfig(ModifierNameEnum::MUSH_SHOWER_MALUS);
        $mushShowerModifier
            ->setTriggeredEvent($eventConfig)
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setTagConstraints([
                ActionEnum::SHOWER => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::WASH_IN_SINK => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierName(ModifierNameEnum::MUSH_SHOWER_MALUS)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($mushShowerModifier);

        $mushConsumeSatietyModifier = new VariableEventModifierConfig('mushConsumeSatietyModifier');
        $mushConsumeSatietyModifier
            ->setTargetVariable(PlayerVariableEnum::SATIETY)
            ->setDelta(4)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setPriority(ModifierPriorityEnum::OVERRIDE_VALUE_PRIORITY)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setApplyOnTarget(true)
            ->setTagConstraints([
                ActionEnum::CONSUME => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::CONSUME_DRUG => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(ModifierNameEnum::MUSH_CONSUME)
        ;
        $manager->persist($mushConsumeSatietyModifier);

        $mushConsumeModifier = new EventModifierConfig('mushConsumeModifier');
        $mushConsumeModifier
            ->setPriority(ModifierPriorityEnum::PREVENT_EVENT)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setApplyOnTarget(true)
            ->setTagConstraints([
                ActionEnum::CONSUME => ModifierRequirementEnum::ALL_TAGS,
                PlayerVariableEnum::HEALTH_POINT => ModifierRequirementEnum::ANY_TAGS,
                PlayerVariableEnum::MORAL_POINT => ModifierRequirementEnum::ANY_TAGS,
                PlayerVariableEnum::MOVEMENT_POINT => ModifierRequirementEnum::ANY_TAGS,
                PlayerVariableEnum::ACTION_POINT => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($mushConsumeModifier);

        $manager->flush();

        $this->addReference(self::FROZEN_MODIFIER, $frozenModifier);
        $this->addReference(self::DISABLED_CONVERSION_MODIFIER, $disabledConversionModifier);
        $this->addReference(self::DISABLED_NOT_ALONE_MODIFIER, $disabledNotAloneModifier);
        $this->addReference(self::PACIFIST_MODIFIER, $pacifistModifier);
        $this->addReference(self::BURDENED_MODIFIER, $burdenedModifier);
        $this->addReference(self::ANTISOCIAL_MODIFIER, $antisocialModifier);
        $this->addReference(self::LOST_MODIFIER, $lostModifier);
        $this->addReference(self::LYING_DOWN_MODIFIER, $lyingDownModifier);
        $this->addReference(self::STARVING_MODIFIER, $starvingModifier);
        $this->addReference(self::INCREASE_CYCLE_DISEASE_CHANCES_30, $increaseCycleDiseaseChances30);

        $this->addReference(self::MUSH_SHOWER_MODIFIER, $mushShowerModifier);
        $this->addReference(self::MUSH_CONSUME_MODIFIER, $mushConsumeModifier);
        $this->addReference(self::MUSH_CONSUME_SATIETY_MODIFIER, $mushConsumeSatietyModifier);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            EventConfigFixtures::class,
        ];
    }
}
