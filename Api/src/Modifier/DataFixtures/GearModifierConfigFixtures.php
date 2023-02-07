<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\QuantityEventInterface;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Player\Enum\PlayerVariableEnum;

class GearModifierConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const APRON_MODIFIER = 'apron_modifier';
    public const ARMOR_MODIFIER = 'armor_modifier';
    public const WRENCH_MODIFIER = 'wrench_modifier';
    public const GLOVES_MODIFIER = 'gloves_modifier';
    public const SOAP_MODIFIER = 'soap_modifier';
    public const SOAP_SINK_MODIFIER = 'soap_sink_modifier';
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
        $apronModifier = new VariableEventModifierConfig();

        $apronModifier
            ->setTargetVariable(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(-100)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::EVENT_DIRTY)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(ModifierNameEnum::APRON_MODIFIER)
        ;
        $apronModifier->buildName();
        $manager->persist($apronModifier);

        $armorModifier = new VariableEventModifierConfig();
        $armorModifier
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setModifierHolderClass(ModifierHolderClassEnum::TARGET_PLAYER)
            ->setTargetEvent(ModifierScopeEnum::INJURY)
            ->setApplyOnParameterOnly(true)
        ;
        $armorModifier->buildName();
        $manager->persist($armorModifier);

        $wrenchModifier = new VariableEventModifierConfig();
        $wrenchModifier
            ->setTargetVariable(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(1.5)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setTargetEvent(ActionTypeEnum::ACTION_TECHNICIAN)
        ;
        $wrenchModifier->buildName();
        $manager->persist($wrenchModifier);

        $glovesModifier = new VariableEventModifierConfig();
        $glovesModifier
            ->setTargetVariable(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(0)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
            ->setTargetEvent(ModifierScopeEnum::EVENT_CLUMSINESS)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(ModifierNameEnum::GLOVES_MODIFIER)
        ;
        $glovesModifier->buildName();
        $manager->persist($glovesModifier);

        $soapModifier = new VariableEventModifierConfig();
        $soapModifier
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionEnum::SHOWER)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
        ;
        $soapModifier->buildName();
        $manager->persist($soapModifier);

        $soapSinkModifier = new VariableEventModifierConfig();
        $soapSinkModifier
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionEnum::WASH_IN_SINK)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
        ;
        $soapSinkModifier->buildName();
        $manager->persist($soapSinkModifier);

        $aimModifier = new VariableEventModifierConfig();
        $aimModifier
            ->setTargetVariable(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(1.1)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionTypeEnum::ACTION_SHOOT)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
        ;
        $aimModifier->buildName();
        $manager->persist($aimModifier);

        $antiGravScooterModifier = new VariableEventModifierConfig();
        $antiGravScooterModifier
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::EVENT_ACTION_MOVEMENT_CONVERSION)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
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
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::EVENT_ACTION_MOVEMENT_CONVERSION)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->addModifierRequirement($evenCyclesActivationRequirement)
        ;
        $rollingBoulderModifier->buildName();
        $manager->persist($rollingBoulderModifier);

        $oscilloscopeSuccessModifier = new VariableEventModifierConfig();
        $oscilloscopeSuccessModifier
            ->setTargetVariable(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(1.5)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionEnum::STRENGTHEN_HULL)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
        ;
        $oscilloscopeSuccessModifier->buildName();
        $manager->persist($oscilloscopeSuccessModifier);

        $strengthenActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::REASON);
        $strengthenActivationRequirement
            ->setActivationRequirement(ActionEnum::STRENGTHEN_HULL)
            ->buildName()
        ;
        $manager->persist($strengthenActivationRequirement);

        $oscilloscopeRepairModifier = new VariableEventModifierConfig();
        $oscilloscopeRepairModifier
            ->setTargetVariable(DaedalusVariableEnum::HULL)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(QuantityEventInterface::CHANGE_VARIABLE)
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->addModifierRequirement($strengthenActivationRequirement)
        ;
        $oscilloscopeRepairModifier->buildName();
        $manager->persist($oscilloscopeRepairModifier);

        $antennaModifier = new VariableEventModifierConfig();
        $antennaModifier
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent('TODO comms. action')
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
        ;
        $antennaModifier->buildName();
        $manager->persist($antennaModifier);

        $gravityConversionModifier = new VariableEventModifierConfig();
        $gravityConversionModifier
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ModifierScopeEnum::EVENT_ACTION_MOVEMENT_CONVERSION)
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
        ;
        $gravityConversionModifier->buildName();
        $manager->persist($gravityConversionModifier);

        $cycleEventActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::REASON);
        $cycleEventActivationRequirement
            ->setActivationRequirement(EventEnum::NEW_CYCLE)
            ->buildName()
        ;
        $manager->persist($cycleEventActivationRequirement);

        $gravityCycleModifier = new VariableEventModifierConfig();
        $gravityCycleModifier
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent(QuantityEventInterface::CHANGE_VARIABLE)
            ->addModifierRequirement($cycleEventActivationRequirement)
        ;
        $gravityCycleModifier->buildName();
        $manager->persist($gravityCycleModifier);

        $oxygenTankModifier = new VariableEventModifierConfig();
        $oxygenTankModifier
            ->setTargetVariable(DaedalusVariableEnum::OXYGEN)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(QuantityEventInterface::CHANGE_VARIABLE)
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->addModifierRequirement($cycleEventActivationRequirement)
        ;
        $oxygenTankModifier->buildName();
        $manager->persist($oxygenTankModifier);

        $manager->flush();

        $this->addReference(self::APRON_MODIFIER, $apronModifier);
        $this->addReference(self::ARMOR_MODIFIER, $armorModifier);
        $this->addReference(self::WRENCH_MODIFIER, $wrenchModifier);
        $this->addReference(self::GLOVES_MODIFIER, $glovesModifier);
        $this->addReference(self::SOAP_MODIFIER, $soapModifier);
        $this->addReference(self::SOAP_SINK_MODIFIER, $soapSinkModifier);
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
        ];
    }
}
