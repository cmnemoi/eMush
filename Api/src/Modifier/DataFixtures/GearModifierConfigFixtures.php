<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Game\DataFixtures\EventConfigFixtures;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\PreventEventModifierConfig;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;

class GearModifierConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const APRON_MODIFIER = 'apron_modifier';
    public const ARMOR_MODIFIER = 'armor_modifier';
    public const WRENCH_MODIFIER = 'wrench_modifier';
    public const GLOVES_MODIFIER = 'gloves_modifier';
    public const SOAP_MODIFIER = 'soap_modifier';
    public const AIM_MODIFIER = 'aim_modifier';
    public const SCOOTER_MODIFIER = 'scooter_modifier';
    public const ROLLING_BOULDER = 'rolling_boulder';
    public const OSCILLOSCOPE_SUCCESS_MODIFIER = 'oscilloscope_success_modifier';
    public const OSCILLOSCOPE_REPAIR_MODIFIER = 'oscilloscope_repair_modifier';
    public const ANTENNA_MODIFIER = 'antenna_modifier';
    public const GRAVITY_CONVERSION_MODIFIER = 'gravity_conversion_modifier';
    public const GRAVITY_CYCLE_MODIFIER = 'gravity_cycle_modifier';
    public const OXYGEN_TANK_MODIFIER = 'oxygen_tank_modifier';

    public function load(ObjectManager $manager): void
    {
        $apronModifier = new PreventEventModifierConfig();

        $apronModifier
            ->setTargetEvent(StatusEvent::STATUS_APPLIED)
            ->setTagConstraints([
                PlayerStatusEnum::DIRTY => ModifierRequirementEnum::ALL_TAGS,
                ActionTypeEnum::ACTION_SUPER_DIRTY => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setModifierName(ModifierNameEnum::APRON_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setName(ModifierNameEnum::APRON_MODIFIER)
        ;
        $manager->persist($apronModifier);

        $armorModifier = new VariableEventModifierConfig();
        $armorModifier
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTagConstraints([
                ActionEnum::HIT => ModifierRequirementEnum::ALL_TAGS,
                ActionOutputEnum::CRITICAL_SUCCESS => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setApplyOnTarget(true)
            ->setModifierRange(ModifierHolderClassEnum::TARGET_PLAYER)
            ->setName('armorReduceDamage')
        ;
        $manager->persist($armorModifier);

        $wrenchModifier = new VariableEventModifierConfig();
        $wrenchModifier
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(1.5)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setTagConstraints([ActionTypeEnum::ACTION_TECHNICIAN => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setName('modifier_for_player_x1.5percentageSuccess_on_roll.technician')
        ;
        $manager->persist($wrenchModifier);

        $glovesModifier = new PreventEventModifierConfig();
        $glovesModifier
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setApplyOnTarget(true)
            ->setTagConstraints([EndCauseEnum::CLUMSINESS => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(ModifierNameEnum::GLOVES_MODIFIER)
            ->setName('preventClumsinessModifier')
        ;
        $manager->persist($glovesModifier);

        $soapModifier = new VariableEventModifierConfig();
        $soapModifier
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([
                ActionEnum::WASH_IN_SINK => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::SHOWER => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setName('soapShowerActionModifier')
        ;
        $manager->persist($soapModifier);

        $aimModifier = new VariableEventModifierConfig();
        $aimModifier
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(1.1)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setTagConstraints([
                ActionEnum::SHOOT => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::SHOOT_HUNTER => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $aimModifier->buildName();
        $manager->persist($aimModifier);

        $antiGravScooterModifier = new VariableEventModifierConfig();
        $antiGravScooterModifier
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([ActionVariableEvent::MOVEMENT_CONVERSION => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(ModifierNameEnum::ANTIGRAV_SCOOTER_CONVERSION_MODIFIER)
        ;
        $antiGravScooterModifier->buildName();
        $manager->persist($antiGravScooterModifier);

        $evenCyclesActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::CYCLE);
        $evenCyclesActivationRequirement
            ->setActivationRequirement(ModifierRequirementEnum::EVEN)
            ->buildName()
        ;
        $manager->persist($evenCyclesActivationRequirement);

        $rollingBoulderModifier = new VariableEventModifierConfig();
        $rollingBoulderModifier
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([ActionVariableEvent::MOVEMENT_CONVERSION => ModifierRequirementEnum::ALL_TAGS])
            ->addModifierRequirement($evenCyclesActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $rollingBoulderModifier->buildName();
        $manager->persist($rollingBoulderModifier);

        $oscilloscopeSuccessModifier = new VariableEventModifierConfig();
        $oscilloscopeSuccessModifier
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(1.5)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setTagConstraints([ActionEnum::STRENGTHEN_HULL => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $oscilloscopeSuccessModifier->buildName();
        $manager->persist($oscilloscopeSuccessModifier);

        $oscilloscopeRepairModifier = new VariableEventModifierConfig();
        $oscilloscopeRepairModifier
            ->setTargetVariable(DaedalusVariableEnum::HULL)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTagConstraints([ActionEnum::STRENGTHEN_HULL => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $oscilloscopeRepairModifier->buildName();
        $manager->persist($oscilloscopeRepairModifier);

        $antennaModifier = new VariableEventModifierConfig();
        $antennaModifier
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
        ;
        $antennaModifier->buildName();
        $manager->persist($antennaModifier);

        $gravityConversionModifier = new VariableEventModifierConfig();
        $gravityConversionModifier
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([ActionVariableEvent::MOVEMENT_CONVERSION => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
        ;
        $gravityConversionModifier->buildName();
        $manager->persist($gravityConversionModifier);

        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigFixtures::MOVEMENT_INCREASE_1);
        $gravityCycleModifier = new TriggerEventModifierConfig();
        $gravityCycleModifier
            ->setTriggeredEvent($eventConfig)
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setApplyOnTarget(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setName('gravityGainMovement')
        ;
        $manager->persist($gravityCycleModifier);

        $oxygenTankModifier = new VariableEventModifierConfig();
        $oxygenTankModifier
            ->setTargetVariable(DaedalusVariableEnum::OXYGEN)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTagConstraints([EventEnum::NEW_CYCLE => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setName('oxygenLossReduction_oxygenTank')
        ;
        $manager->persist($oxygenTankModifier);

        $manager->flush();

        $this->addReference(self::APRON_MODIFIER, $apronModifier);
        $this->addReference(self::ARMOR_MODIFIER, $armorModifier);
        $this->addReference(self::WRENCH_MODIFIER, $wrenchModifier);
        $this->addReference(self::GLOVES_MODIFIER, $glovesModifier);
        $this->addReference(self::SOAP_MODIFIER, $soapModifier);
        $this->addReference(self::AIM_MODIFIER, $aimModifier);
        $this->addReference(self::SCOOTER_MODIFIER, $antiGravScooterModifier);
        $this->addReference(self::ROLLING_BOULDER, $rollingBoulderModifier);
        $this->addReference(self::OSCILLOSCOPE_SUCCESS_MODIFIER, $oscilloscopeSuccessModifier);
        $this->addReference(self::OSCILLOSCOPE_REPAIR_MODIFIER, $oscilloscopeRepairModifier);
        $this->addReference(self::ANTENNA_MODIFIER, $antennaModifier);
        $this->addReference(self::GRAVITY_CONVERSION_MODIFIER, $gravityConversionModifier);
        $this->addReference(self::GRAVITY_CYCLE_MODIFIER, $gravityCycleModifier);
        $this->addReference(self::OXYGEN_TANK_MODIFIER, $oxygenTankModifier);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            EventConfigFixtures::class,
        ];
    }
}
