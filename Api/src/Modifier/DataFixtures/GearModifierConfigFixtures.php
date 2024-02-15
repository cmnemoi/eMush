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
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\DataFixtures\EventConfigFixtures;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\ModifierStrategyEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Service\PlayerService;
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
    public const AIM_HUNTER_MODIFIER = 'aim_hunter_modifier';
    public const SCOOTER_MODIFIER = 'scooter_modifier';
    public const ROLLING_BOULDER = 'rolling_boulder';
    public const OSCILLOSCOPE_SUCCESS_MODIFIER = 'oscilloscope_success_modifier';
    public const OSCILLOSCOPE_SUCCESS_MODIFIER_RENOVATE_ACTION = 'oscilloscope_success_modifier_renovate_action';
    public const OSCILLOSCOPE_REPAIR_MODIFIER = 'oscilloscope_repair_modifier';
    public const ANTENNA_MODIFIER = 'antenna_modifier';
    public const GRAVITY_CONVERSION_MODIFIER = 'gravity_conversion_modifier';
    public const GRAVITY_CYCLE_MODIFIER = 'gravity_cycle_modifier';
    public const OXYGEN_TANK_MODIFIER = 'oxygen_tank_modifier';
    public const PLANET_SCANNER_MODIFIER = 'planet_scanner_modifier';
    public const LIQUID_MAP_MODIFIER = 'liquid_map_modifier';
    public const LIQUID_MAP_MODIFIER_RANDOM_50 = 'liquid_map_modifier_random_50';
    public const ALIEN_OIL_INCREASE_FUEL_INJECTED = 'alien_oil_increase_fuel_injected';

    public function load(ObjectManager $manager): void
    {
        $apronModifier = new EventModifierConfig(ModifierNameEnum::APRON_MODIFIER);

        $apronModifier
            ->setTargetEvent(StatusEvent::STATUS_APPLIED)
            ->setTagConstraints([
                PlayerStatusEnum::DIRTY => ModifierRequirementEnum::ALL_TAGS,
                ActionTypeEnum::ACTION_SUPER_DIRTY => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setPriority(ModifierPriorityEnum::PREVENT_EVENT)
            ->setModifierStrategy(ModifierStrategyEnum::PREVENT_EVENT)
            ->setModifierName(ModifierNameEnum::APRON_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($apronModifier);

        $armorModifier = new VariableEventModifierConfig('armorReduceDamage');
        $armorModifier
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTagConstraints([
                ActionEnum::HIT => ModifierRequirementEnum::ANY_TAGS,
                PlanetSectorEvent::FIGHT => ModifierRequirementEnum::ANY_TAGS,
                ActionOutputEnum::CRITICAL_SUCCESS => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setApplyOnTarget(true)
            ->setModifierRange(ModifierHolderClassEnum::TARGET_PLAYER)
        ;
        $manager->persist($armorModifier);

        $wrenchModifier = new VariableEventModifierConfig('modifier_for_player_x1.5percentageSuccess_on_roll.technician');
        $wrenchModifier
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(1.5)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setTagConstraints([ActionTypeEnum::ACTION_TECHNICIAN => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($wrenchModifier);

        $glovesModifier = new EventModifierConfig('preventClumsinessModifier');
        $glovesModifier
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setApplyOnTarget(true)
            ->setTagConstraints([EndCauseEnum::CLUMSINESS => ModifierRequirementEnum::ALL_TAGS])
            ->setPriority(ModifierPriorityEnum::PREVENT_EVENT)
            ->setModifierStrategy(ModifierStrategyEnum::PREVENT_EVENT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(ModifierNameEnum::GLOVES_MODIFIER)
        ;
        $manager->persist($glovesModifier);

        $soapModifier = new VariableEventModifierConfig('soapShowerActionModifier');
        $soapModifier
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([
                ActionEnum::WASH_IN_SINK => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::SHOWER => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($soapModifier);

        $aimModifier = new VariableEventModifierConfig('increaseShootPercentage33Percent');
        $aimModifier
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(1.33)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setTagConstraints([
                ActionTypeEnum::ACTION_SHOOT => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($aimModifier);

        $aimHunterModifier = new VariableEventModifierConfig('modifier_for_player_x1.1percentage_on_action_shoot_hunter');
        $aimHunterModifier
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(1.1)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setTagConstraints([
                ActionEnum::SHOOT_HUNTER => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($aimHunterModifier);

        $antiGravScooterModifier = new VariableEventModifierConfig(ModifierNameEnum::ANTIGRAV_SCOOTER_CONVERSION_MODIFIER);
        $antiGravScooterModifier
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([ActionEnum::CONVERT_ACTION_TO_MOVEMENT => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(ModifierNameEnum::ANTIGRAV_SCOOTER_CONVERSION_MODIFIER)
        ;
        $manager->persist($antiGravScooterModifier);

        $evenCyclesActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::CYCLE);
        $evenCyclesActivationRequirement
            ->setActivationRequirement(ModifierRequirementEnum::EVEN)
            ->buildName()
        ;
        $manager->persist($evenCyclesActivationRequirement);

        $rollingBoulderModifier = new VariableEventModifierConfig('rollingBoulderConversionModifier');
        $rollingBoulderModifier
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([ActionEnum::CONVERT_ACTION_TO_MOVEMENT => ModifierRequirementEnum::ALL_TAGS])
            ->addModifierRequirement($evenCyclesActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($rollingBoulderModifier);

        $oscilloscopeSuccessModifier = new VariableEventModifierConfig('wavoscopeActionPercentageIncrease50Percents');
        $oscilloscopeSuccessModifier
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(1.5)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setTagConstraints([ActionEnum::STRENGTHEN_HULL => ModifierRequirementEnum::ANY_TAGS])
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($oscilloscopeSuccessModifier);

        $oscilloscopeSuccessModifierRenovateAction = new VariableEventModifierConfig('wavoscopeRenovateActionPercentageIncrease100Percents');
        $oscilloscopeSuccessModifierRenovateAction
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(2.0)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setTagConstraints([ActionEnum::RENOVATE => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($oscilloscopeSuccessModifierRenovateAction);

        $oscilloscopeRepairModifier = new VariableEventModifierConfig('wavoscopeRepairIncreaseBy2');
        $oscilloscopeRepairModifier
            ->setTargetVariable(DaedalusVariableEnum::HULL)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTagConstraints([ActionEnum::STRENGTHEN_HULL => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
        ;
        $manager->persist($oscilloscopeRepairModifier);

        $antennaModifier = new VariableEventModifierConfig('decreaseCommunicationActionCost1Action');
        $antennaModifier
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
        ;
        $manager->persist($antennaModifier);

        $gravityConversionModifier = new VariableEventModifierConfig('gravityDecreaseMovementConversionGain1Movement');
        $gravityConversionModifier
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([ActionEnum::CONVERT_ACTION_TO_MOVEMENT => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
        ;
        $manager->persist($gravityConversionModifier);

        $gravityCycleModifier = new VariableEventModifierConfig('gravityDecreaseMovementGainOnNewCycle');
        $gravityCycleModifier
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTagConstraints([PlayerService::BASE_PLAYER_CYCLE_CHANGE => ModifierRequirementEnum::ALL_TAGS])
            ->setApplyOnTarget(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
        ;

        $manager->persist($gravityCycleModifier);

        $oxygenTankModifier = new VariableEventModifierConfig('oxygenLossReduction_oxygenTank');
        $oxygenTankModifier
            ->setTargetVariable(DaedalusVariableEnum::OXYGEN)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([EventEnum::NEW_CYCLE => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
        ;
        $manager->persist($oxygenTankModifier);

        $planetScannerModifier = new VariableEventModifierConfig('modifier_for_daedalus_+30percentage_on_action_scan');
        $planetScannerModifier
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(30)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([ActionEnum::SCAN => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
        ;
        $manager->persist($planetScannerModifier);

        $liquidMapModifier = new VariableEventModifierConfig('modifier_for_place_+1sector_revealed_on_action_scan_planet');
        $liquidMapModifier
            ->setTargetVariable(ActionVariableEnum::OUTPUT_QUANTITY)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::GET_OUTPUT_QUANTITY)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([
                ActionEnum::SCAN => ModifierRequirementEnum::ANY_TAGS,
                ActionOutputEnum::FAIL => ModifierRequirementEnum::NONE_TAGS,
                ModifierNameEnum::LIQUID_MAP_MODIFIER => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLACE)
            ->setModifierName(ModifierNameEnum::LIQUID_MAP_MODIFIER)
        ;
        $manager->persist($liquidMapModifier);

        $random50 = $this->getReference(DiseaseModifierConfigFixtures::RANDOM_50);

        $liquidMapRandom50Modifier = new VariableEventModifierConfig('modifier_for_place_+1sector_revealed_on_action_scan_planet_random_50');
        $liquidMapRandom50Modifier
            ->setTargetVariable(ActionVariableEnum::OUTPUT_QUANTITY)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::GET_OUTPUT_QUANTITY)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([
                ActionEnum::SCAN => ModifierRequirementEnum::ANY_TAGS,
                ActionOutputEnum::FAIL => ModifierRequirementEnum::NONE_TAGS,
                ModifierNameEnum::LIQUID_MAP_MODIFIER . '_random_50' => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLACE)
            ->setModifierName(ModifierNameEnum::LIQUID_MAP_MODIFIER . '_random_50')
            ->setModifierActivationRequirements([$random50])
        ;
        $manager->persist($liquidMapRandom50Modifier);

        $alienOilIncreaseFuelInjected = new VariableEventModifierConfig('alien_oil_increase_fuel_injected');
        $alienOilIncreaseFuelInjected
            ->setTargetVariable(ActionVariableEnum::OUTPUT_QUANTITY)
            ->setDelta(4)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::GET_OUTPUT_QUANTITY)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setApplyOnTarget(true)
            ->setTagConstraints([
                ActionEnum::INSERT_FUEL_CHAMBER => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::INSERT_FUEL => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::EQUIPMENT)
        ;
        $manager->persist($alienOilIncreaseFuelInjected);

        $manager->flush();

        $this->addReference(self::APRON_MODIFIER, $apronModifier);
        $this->addReference(self::ARMOR_MODIFIER, $armorModifier);
        $this->addReference(self::WRENCH_MODIFIER, $wrenchModifier);
        $this->addReference(self::GLOVES_MODIFIER, $glovesModifier);
        $this->addReference(self::SOAP_MODIFIER, $soapModifier);
        $this->addReference(self::AIM_MODIFIER, $aimModifier);
        $this->addReference(self::AIM_HUNTER_MODIFIER, $aimHunterModifier);
        $this->addReference(self::SCOOTER_MODIFIER, $antiGravScooterModifier);
        $this->addReference(self::ROLLING_BOULDER, $rollingBoulderModifier);
        $this->addReference(self::OSCILLOSCOPE_SUCCESS_MODIFIER, $oscilloscopeSuccessModifier);
        $this->addReference(self::OSCILLOSCOPE_SUCCESS_MODIFIER_RENOVATE_ACTION, $oscilloscopeSuccessModifierRenovateAction);
        $this->addReference(self::OSCILLOSCOPE_REPAIR_MODIFIER, $oscilloscopeRepairModifier);
        $this->addReference(self::ANTENNA_MODIFIER, $antennaModifier);
        $this->addReference(self::GRAVITY_CONVERSION_MODIFIER, $gravityConversionModifier);
        $this->addReference(self::GRAVITY_CYCLE_MODIFIER, $gravityCycleModifier);
        $this->addReference(self::OXYGEN_TANK_MODIFIER, $oxygenTankModifier);
        $this->addReference(self::PLANET_SCANNER_MODIFIER, $planetScannerModifier);
        $this->addReference(self::LIQUID_MAP_MODIFIER, $liquidMapModifier);
        $this->addReference(self::LIQUID_MAP_MODIFIER_RANDOM_50, $liquidMapRandom50Modifier);
        $this->addReference(self::ALIEN_OIL_INCREASE_FUEL_INJECTED, $alienOilIncreaseFuelInjected);
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
