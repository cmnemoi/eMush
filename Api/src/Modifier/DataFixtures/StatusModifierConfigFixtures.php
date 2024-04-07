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
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\DataFixtures\EventConfigFixtures;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Event\RollPercentageEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\Config\EventModifierConfig;
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
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Enum\EquipmentStatusEnum;

class StatusModifierConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const string FROZEN_MODIFIER = 'frozen_modifier';
    public const string DISABLED_CONVERSION_MODIFIER = 'disabled_conversion_modifier';
    public const string DISABLED_NOT_ALONE_MODIFIER = 'disabled_not_alone_modifier';
    public const string PACIFIST_MODIFIER = 'pacifist_modifier';
    public const string BURDENED_MODIFIER = 'burdened_modifier';
    public const string ANTISOCIAL_MODIFIER = 'antisocial_modifier';
    public const string LOST_MODIFIER = 'lost_modifier';
    public const string LYING_DOWN_MODIFIER = 'lying_down_modifier';
    public const string STARVING_MODIFIER = 'starving_modifier';

    public const string INCREASE_CYCLE_DISEASE_CHANCES_30 = 'increase_cycle_disease_chances_30';
    public const string MUSH_SHOWER_MODIFIER = 'mush_shower_modifier';
    public const string MUSH_CONSUME_SATIETY_MODIFIER = 'mush_consume_satiety_modifier';
    public const string MUSH_CONSUME_MODIFIER = 'mush_consume_modifier';
    public const string MUSH_MORALE_MODIFIER = 'mush_morale_modifier';

    public const string SHOOTER_SPECIALIST_POINT = 'shooter_specialist_point';
    public const string ASTRONAVIGATION_NERON_CPU_PRIORITY_MODIFIER_PLUS_1_SECTION = 'astronavigation_neron_cpu_priority_modifier_plus_1_section';
    public const string ASTRONAVIGATION_NERON_CPU_PRIORITY_MODIFIER_MINUS_1_ACTION_POINT = 'astronavigation_neron_cpu_priority_modifier_minus_1_action_point';

    public const string DEFENCE_NERON_CPU_PRIORITY_INCREASED_TURRET_CHARGE = 'defence_neron_cpu_priority_modifier_increased_turret_max_charge';
    public const string DEFENCE_NERON_CPU_PRIORITY_INCREASED_TURRET_RECHARGE_RATE = 'defence_neron_cpu_priority_modifier_increased_recharge_rate';
    public const string IMMUNIZED_MODIFIER_SET_0_SPORES_ON_CHANGE_VARIABLE = 'immunized_modifier_set_0_spores_on_change_variable';

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

        /** @var AbstractEventConfig $eventConfigIncreaseMaxCharge */
        $eventConfigIncreaseMaxCharge = $this->getReference(EventConfigFixtures::MORAL_REDUCE_1);
        $antisocialModifier = new TriggerEventModifierConfig(ModifierNameEnum::ANTISOCIAL_MODIFIER);
        $antisocialModifier
            ->setTriggeredEvent($eventConfigIncreaseMaxCharge)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setApplyOnTarget(true)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->addModifierRequirement($notAloneActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(ModifierNameEnum::ANTISOCIAL_MODIFIER)
        ;
        $manager->persist($antisocialModifier);

        /** @var AbstractEventConfig $eventConfigIncreaseMaxCharge */
        $eventConfigIncreaseMaxCharge = $this->getReference(EventConfigFixtures::MORAL_REDUCE_2);
        $lostModifier = new TriggerEventModifierConfig('lostModifier');
        $lostModifier
            ->setTriggeredEvent($eventConfigIncreaseMaxCharge)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setApplyOnTarget(true)
            ->setModifierName(ModifierNameEnum::LOST_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($lostModifier);

        $lyingDownModifier = new VariableEventModifierConfig(ModifierNameEnum::LYING_DOWN_MODIFIER);
        $lyingDownModifier
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setApplyOnTarget(true)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTagConstraints(['base_player_cycle_change' => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $lyingDownModifier->buildName();
        $manager->persist($lyingDownModifier);

        /** @var AbstractEventConfig $eventConfigIncreaseMaxCharge */
        $eventConfigIncreaseMaxCharge = $this->getReference(EventConfigFixtures::HEALTH_REDUCE_1);
        $starvingModifier = new TriggerEventModifierConfig('starvingModifier');
        $starvingModifier
            ->setTriggeredEvent($eventConfigIncreaseMaxCharge)
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

        /** @var AbstractEventConfig $eventConfigIncreaseMaxCharge */
        $eventConfigIncreaseMaxCharge = $this->getReference(EventConfigFixtures::HEALTH_REDUCE_3);
        $mushShowerModifier = new TriggerEventModifierConfig(ModifierNameEnum::MUSH_SHOWER_MALUS);
        $mushShowerModifier
            ->setTriggeredEvent($eventConfigIncreaseMaxCharge)
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
            ->setModifierStrategy(ModifierStrategyEnum::PREVENT_EVENT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($mushConsumeModifier);

        $shooterSpecialist = new VariableEventModifierConfig('modifier_shooter_specialist_point');
        $shooterSpecialist
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(0)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setPriority(ModifierPriorityEnum::OVERRIDE_VALUE_PRIORITY)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setApplyOnTarget(false)
            ->setTagConstraints([
                ActionTypeEnum::ACTION_SHOOT_HUNTER => ModifierRequirementEnum::ANY_TAGS,
                ActionTypeEnum::ACTION_SHOOT => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(ModifierNameEnum::SHOOTER_SPECIALIST_POINT)
        ;
        $manager->persist($shooterSpecialist);

        $mushMoraleModifier = new VariableEventModifierConfig('mushMoraleModifier');
        $mushMoraleModifier
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(0)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setPriority(ModifierPriorityEnum::PREVENT_EVENT)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setApplyOnTarget(true)
            ->setTagConstraints([
                PlayerVariableEnum::MORAL_POINT => ModifierRequirementEnum::ALL_TAGS,
                VariableEventInterface::LOSS => ModifierRequirementEnum::ALL_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($mushMoraleModifier);

        $astronavigationNeronCpuPriorityModifierPlus1Section = new VariableEventModifierConfig('astronavigationNeronCpuPriorityModifierPlus1Section');
        $astronavigationNeronCpuPriorityModifierPlus1Section
            ->setTargetVariable(ActionVariableEnum::OUTPUT_QUANTITY)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::GET_OUTPUT_QUANTITY)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([ActionEnum::ANALYZE_PLANET => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
        ;
        $manager->persist($astronavigationNeronCpuPriorityModifierPlus1Section);

        $astronavigationNeronCpuPriorityModifierMinus1ActionPoint = new VariableEventModifierConfig('astronavigationNeronCpuPriorityModifierMinus1ActionPoint');
        $astronavigationNeronCpuPriorityModifierMinus1ActionPoint
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([ActionEnum::ANALYZE_PLANET => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
        ;
        $manager->persist($astronavigationNeronCpuPriorityModifierMinus1ActionPoint);

        $eventConfigIncreaseMaxCharge = new VariableEventConfig();
        $eventConfigIncreaseMaxCharge
            ->setTargetVariable(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVariableHolderClass(ModifierHolderClassEnum::EQUIPMENT)
            ->setQuantity(2)
            ->setEventName(VariableEventInterface::CHANGE_VALUE_MAX)
            ->setName('increase_turret_max_charges_event_config_test')
        ;
        $manager->persist($eventConfigIncreaseMaxCharge);

        $modifierRequirementNameTurret = new ModifierActivationRequirement(ModifierRequirementEnum::HOLDER_NAME);
        $modifierRequirementNameTurret
            ->setActivationRequirement(EquipmentEnum::TURRET_COMMAND)
            ->setName('modifier_requirement_name_turret_test')
        ;
        $manager->persist($modifierRequirementNameTurret);

        $defenceCpuPriorityIncreaseTurretMaxCharge = new DirectModifierConfig('defenceCpuPriorityIncreaseTurretMaxCharge');
        $defenceCpuPriorityIncreaseTurretMaxCharge
            ->setTriggeredEvent($eventConfigIncreaseMaxCharge)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([$modifierRequirementNameTurret])
        ;
        $manager->persist($defenceCpuPriorityIncreaseTurretMaxCharge);

        $defenceCpuPriorityIncreaseTurretRecharge = new VariableEventModifierConfig('defenceCpuPriorityIncreaseTurretRecharge');
        $defenceCpuPriorityIncreaseTurretRecharge
            ->setTargetVariable(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([
                EquipmentEnum::TURRET_COMMAND => ModifierRequirementEnum::ALL_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
        ;
        $manager->persist($defenceCpuPriorityIncreaseTurretRecharge);

        $immunizedModifierSet0SporesOnChangeVariable = new VariableEventModifierConfig('immunizedModifierSet0SporesOnChangeVariable');
        $immunizedModifierSet0SporesOnChangeVariable
            ->setTargetVariable(PlayerVariableEnum::SPORE)
            ->setDelta(0)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setPriority(ModifierPriorityEnum::OVERRIDE_VALUE_PRIORITY)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setApplyOnTarget(true)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($immunizedModifierSet0SporesOnChangeVariable);

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
        $this->addReference(self::MUSH_MORALE_MODIFIER, $mushMoraleModifier);

        $this->addReference(self::SHOOTER_SPECIALIST_POINT, $shooterSpecialist);
        $this->addReference(self::ASTRONAVIGATION_NERON_CPU_PRIORITY_MODIFIER_PLUS_1_SECTION, $astronavigationNeronCpuPriorityModifierPlus1Section);
        $this->addReference(self::ASTRONAVIGATION_NERON_CPU_PRIORITY_MODIFIER_MINUS_1_ACTION_POINT, $astronavigationNeronCpuPriorityModifierMinus1ActionPoint);
        $this->addReference(self::DEFENCE_NERON_CPU_PRIORITY_INCREASED_TURRET_CHARGE, $defenceCpuPriorityIncreaseTurretMaxCharge);
        $this->addReference(self::DEFENCE_NERON_CPU_PRIORITY_INCREASED_TURRET_RECHARGE_RATE, $defenceCpuPriorityIncreaseTurretRecharge);

        $this->addReference(self::IMMUNIZED_MODIFIER_SET_0_SPORES_ON_CHANGE_VARIABLE, $immunizedModifierSet0SporesOnChangeVariable);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            EventConfigFixtures::class,
        ];
    }
}
