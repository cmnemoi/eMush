<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Enum\EventEnum;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Player\Enum\PlayerVariableEnum;
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
    public const MUSH_CONSUME_MORAL_MODIFIER = 'mush_consume_moral_modifier';
    public const MUSH_CONSUME_HEALTH_MODIFIER = 'mush_consume_health_modifier';
    public const MUSH_CONSUME_ACTION_MODIFIER = 'mush_consume_action_modifier';
    public const MUSH_CONSUME_MOVEMENT_MODIFIER = 'mush_consume_movement_modifier';

    public function load(ObjectManager $manager): void
    {
        $frozenModifier = new VariableEventModifierConfig();

        $frozenModifier
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionEnum::CONSUME)
            ->setApplyOnTarget(true)
            ->setModifierRange(ModifierHolderClassEnum::EQUIPMENT)
        ;
        $frozenModifier->buildName();
        $manager->persist($frozenModifier);

        $disabledConversionModifier = new VariableEventModifierConfig();
        $disabledConversionModifier
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::EVENT_ACTION_MOVEMENT_CONVERSION)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $disabledConversionModifier->buildName();
        $manager->persist($disabledConversionModifier);

        $notAloneActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::PLAYER_IN_ROOM);
        $notAloneActivationRequirement
            ->setActivationRequirement(ModifierRequirementEnum::NOT_ALONE)
            ->buildName()
        ;
        $manager->persist($notAloneActivationRequirement);

        $disabledNotAloneModifier = new VariableEventModifierConfig();
        $disabledNotAloneModifier
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionEnum::MOVE)
            ->addModifierRequirement($notAloneActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $disabledNotAloneModifier->buildName();
        $manager->persist($disabledNotAloneModifier);

        $pacifistModifier = new VariableEventModifierConfig();
        $pacifistModifier
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionTypeEnum::ACTION_AGGRESSIVE)
            ->setModifierRange(ModifierHolderClassEnum::PLACE)
        ;
        $pacifistModifier->buildName();
        $manager->persist($pacifistModifier);

        $burdenedModifier = new VariableEventModifierConfig();
        $burdenedModifier
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionEnum::MOVE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $burdenedModifier->buildName();
        $manager->persist($burdenedModifier);

        $antisocialModifier = new VariableEventModifierConfig();
        $antisocialModifier
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->addModifierRequirement($notAloneActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(ModifierNameEnum::ANTISOCIAL_MODIFIER)
        ;
        $antisocialModifier->buildName();
        $manager->persist($antisocialModifier);

        $lostModifier = new VariableEventModifierConfig();
        $lostModifier
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $lostModifier->buildName();
        $manager->persist($lostModifier);

        $lyingDownModifier = new VariableEventModifierConfig();
        $lyingDownModifier
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(ModifierNameEnum::LYING_DOWN_MODIFIER)
        ;
        $lyingDownModifier->buildName();
        $manager->persist($lyingDownModifier);

        $starvingModifier = new VariableEventModifierConfig();
        $starvingModifier
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(ModifierNameEnum::STARVING)
        ;
        $starvingModifier->buildName();
        $manager->persist($starvingModifier);

        $increaseCycleDiseaseChances30 = new VariableEventModifierConfig();
        $increaseCycleDiseaseChances30
            ->setTargetVariable(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(30)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(PlayerEvent::CYCLE_DISEASE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $increaseCycleDiseaseChances30->buildName();
        $manager->persist($increaseCycleDiseaseChances30);

        $showerActionActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::REASON);
        $showerActionActivationRequirement
            ->setActivationRequirement(ActionEnum::SHOWER)
            ->buildName()
        ;
        $manager->persist($showerActionActivationRequirement);

        $mushShowerModifier = new VariableEventModifierConfig();
        $mushShowerModifier
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-3)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->addModifierRequirement($showerActionActivationRequirement)
            ->setModifierName(ModifierNameEnum::MUSH_SHOWER_MALUS)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $mushShowerModifier->buildName();
        $manager->persist($mushShowerModifier);

        $sinkActionActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::REASON);
        $sinkActionActivationRequirement
            ->setActivationRequirement(ActionEnum::WASH_IN_SINK)
            ->buildName()
        ;
        $manager->persist($sinkActionActivationRequirement);

        $mushSinkModifier = new VariableEventModifierConfig();
        $mushSinkModifier
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-3)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->addModifierRequirement($sinkActionActivationRequirement)
            ->setModifierName(ModifierNameEnum::MUSH_SHOWER_MALUS)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $mushSinkModifier->buildName();
        $manager->persist($mushSinkModifier);

        /** @var ModifierActivationRequirement $consumeActionActivationRequirement */
        $consumeActionActivationRequirement = $this->getReference(DiseaseModifierConfigFixtures::REASON_CONSUME);

        $mushConsumeSatietyModifier = new VariableEventModifierConfig();
        $mushConsumeSatietyModifier
            ->setTargetVariable(PlayerVariableEnum::SATIETY)
            ->setDelta(4)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->addModifierRequirement($consumeActionActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $mushConsumeSatietyModifier->buildName();
        $manager->persist($mushConsumeSatietyModifier);

        $mushConsumeHealthModifier = new VariableEventModifierConfig();
        $mushConsumeHealthModifier
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(0)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->addModifierRequirement($consumeActionActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $mushConsumeHealthModifier->buildName();
        $manager->persist($mushConsumeHealthModifier);

        $mushConsumeMoralModifier = new VariableEventModifierConfig();
        $mushConsumeMoralModifier
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(0)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->addModifierRequirement($consumeActionActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $mushConsumeMoralModifier->buildName();
        $manager->persist($mushConsumeMoralModifier);

        $mushConsumeActionModifier = new VariableEventModifierConfig();
        $mushConsumeActionModifier
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(0)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->addModifierRequirement($consumeActionActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $mushConsumeActionModifier->buildName();
        $manager->persist($mushConsumeActionModifier);

        $mushConsumeMovementModifier = new VariableEventModifierConfig();
        $mushConsumeMovementModifier
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(0)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->addModifierRequirement($consumeActionActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $mushConsumeMovementModifier->buildName();
        $manager->persist($mushConsumeMovementModifier);

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
        $this->addReference(self::MUSH_CONSUME_ACTION_MODIFIER, $mushConsumeActionModifier);
        $this->addReference(self::MUSH_CONSUME_MOVEMENT_MODIFIER, $mushConsumeMovementModifier);
        $this->addReference(self::MUSH_CONSUME_HEALTH_MODIFIER, $mushConsumeHealthModifier);
        $this->addReference(self::MUSH_CONSUME_MORAL_MODIFIER, $mushConsumeMoralModifier);
        $this->addReference(self::MUSH_CONSUME_SATIETY_MODIFIER, $mushConsumeSatietyModifier);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            DiseaseModifierConfigFixtures::class,
        ];
    }
}
