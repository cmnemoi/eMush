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
use Mush\Game\Event\QuantityEventInterface;
use Mush\Modifier\Entity\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
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
        $frozenModifier = new ModifierConfig();

        $frozenModifier
            ->setTargetEvent(ActionEnum::CONSUME)
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setModifierHolderClass(ModifierHolderClassEnum::EQUIPMENT)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->buildName()
        ;
        $manager->persist($frozenModifier);

        $disabledConversionModifier = new ModifierConfig();
        $disabledConversionModifier
            ->setTargetEvent(ModifierScopeEnum::EVENT_ACTION_MOVEMENT_CONVERSION)
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-2)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->buildName()
        ;
        $manager->persist($disabledConversionModifier);

        $notAloneActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::PLAYER_IN_ROOM);
        $notAloneActivationRequirement
            ->setActivationRequirement(ModifierRequirementEnum::NOT_ALONE)
            ->buildName()
        ;
        $manager->persist($notAloneActivationRequirement);

        $disabledNotAloneModifier = new ModifierConfig();
        $disabledNotAloneModifier
            ->setTargetEvent(ActionEnum::MOVE)
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-1)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->addModifierRequirement($notAloneActivationRequirement)
            ->buildName()
        ;
        $manager->persist($disabledNotAloneModifier);

        $pacifistModifier = new ModifierConfig();
        $pacifistModifier
            ->setTargetEvent(ActionTypeEnum::ACTION_AGGRESSIVE)
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(2)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLACE)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->buildName()
        ;
        $manager->persist($pacifistModifier);

        $burdenedModifier = new ModifierConfig();
        $burdenedModifier
            ->setTargetEvent(ActionEnum::MOVE)
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(2)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->buildName()
        ;
        $manager->persist($burdenedModifier);

        $antisocialModifier = new ModifierConfig();
        $antisocialModifier
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->addModifierRequirement($notAloneActivationRequirement)
            ->setModifierName(ModifierNameEnum::ANTISOCIAL_MODIFIER)
            ->buildName()
        ;
        $manager->persist($antisocialModifier);

        $lostModifier = new ModifierConfig();
        $lostModifier
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->buildName()
        ;
        $manager->persist($lostModifier);

        $lyingDownModifier = new ModifierConfig();
        $lyingDownModifier
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(ModifierNameEnum::LYING_DOWN_MODIFIER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->buildName()
        ;
        $manager->persist($lyingDownModifier);

        $starvingModifier = new ModifierConfig();
        $starvingModifier
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-1)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setModifierName(ModifierNameEnum::STARVING)
            ->buildName()
        ;
        $manager->persist($starvingModifier);

        $increaseCycleDiseaseChances30 = new ModifierConfig();
        $increaseCycleDiseaseChances30
            ->setTargetEvent(PlayerEvent::CYCLE_DISEASE)
            ->setTargetVariable(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(30)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->buildName()
        ;
        $manager->persist($increaseCycleDiseaseChances30);

        $showerActionActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::REASON);
        $showerActionActivationRequirement
            ->setActivationRequirement(ActionEnum::SHOWER)
            ->buildName()
        ;
        $manager->persist($showerActionActivationRequirement);

        $mushShowerModifier = new ModifierConfig();
        $mushShowerModifier
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-3)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierRequirement($showerActionActivationRequirement)
            ->setModifierName(ModifierNameEnum::MUSH_SHOWER_MALUS)
            ->buildName()
        ;
        $manager->persist($mushShowerModifier);

        $sinkActionActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::REASON);
        $sinkActionActivationRequirement
            ->setActivationRequirement(ActionEnum::WASH_IN_SINK)
            ->buildName()
        ;
        $manager->persist($sinkActionActivationRequirement);

        $mushSinkModifier = new ModifierConfig();
        $mushSinkModifier
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-3)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierRequirement($sinkActionActivationRequirement)
            ->setModifierName(ModifierNameEnum::MUSH_SHOWER_MALUS)
            ->buildName()
        ;
        $manager->persist($mushSinkModifier);

        /** @var ModifierActivationRequirement $consumeActionActivationRequirement */
        $consumeActionActivationRequirement = $this->getReference(DiseaseModifierConfigFixtures::REASON_CONSUME);

        $mushConsumeSatietyModifier = new ModifierConfig();
        $mushConsumeSatietyModifier
            ->setTargetEvent(QuantityEventInterface::CHANGE_VARIABLE)
            ->setTargetVariable(PlayerVariableEnum::SATIETY)
            ->setDelta(4)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierRequirement($consumeActionActivationRequirement)
            ->buildName()
        ;
        $manager->persist($mushConsumeSatietyModifier);

        $mushConsumeHealthModifier = new ModifierConfig();
        $mushConsumeHealthModifier
            ->setTargetEvent(QuantityEventInterface::CHANGE_VARIABLE)
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(0)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierRequirement($consumeActionActivationRequirement)
            ->buildName()
        ;
        $manager->persist($mushConsumeHealthModifier);

        $mushConsumeMoralModifier = new ModifierConfig();
        $mushConsumeMoralModifier
            ->setTargetEvent(QuantityEventInterface::CHANGE_VARIABLE)
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(0)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierRequirement($consumeActionActivationRequirement)
            ->buildName()
        ;
        $manager->persist($mushConsumeMoralModifier);

        $mushConsumeActionModifier = new ModifierConfig();
        $mushConsumeActionModifier
            ->setTargetEvent(QuantityEventInterface::CHANGE_VARIABLE)
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(0)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierRequirement($consumeActionActivationRequirement)
            ->buildName()
        ;
        $manager->persist($mushConsumeActionModifier);

        $mushConsumeMovementModifier = new ModifierConfig();
        $mushConsumeMovementModifier
            ->setTargetEvent(QuantityEventInterface::CHANGE_VARIABLE)
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(0)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierRequirement($consumeActionActivationRequirement)
            ->buildName()
        ;
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
