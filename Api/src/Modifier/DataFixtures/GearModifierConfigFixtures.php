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
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\DataFixtures\EventConfigFixtures;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Hunter\Enum\HunterVariableEnum;
use Mush\Modifier\ConfigData\ModifierConfigData;
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
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Service\PlayerService;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;

class GearModifierConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const string APRON_MODIFIER = 'apron_modifier';
    public const string ARMOR_MODIFIER = 'armor_modifier';
    public const string WRENCH_MODIFIER = 'wrench_modifier';
    public const string GLOVES_MODIFIER = 'gloves_modifier';
    public const string SOAP_MODIFIER = 'soap_modifier';
    public const string AIM_MODIFIER = 'aim_modifier';
    public const string AIM_HUNTER_MODIFIER = 'aim_hunter_modifier';
    public const string SCOOTER_MODIFIER = 'scooter_modifier';
    public const string ROLLING_BOULDER = 'rolling_boulder';
    public const string OSCILLOSCOPE_SUCCESS_MODIFIER = 'oscilloscope_success_modifier';
    public const string OSCILLOSCOPE_SUCCESS_MODIFIER_RENOVATE_ACTION = 'oscilloscope_success_modifier_renovate_action';
    public const string OSCILLOSCOPE_REPAIR_MODIFIER = 'oscilloscope_repair_modifier';
    public const string ANTENNA_MODIFIER = 'antenna_modifier';
    public const string GRAVITY_CONVERSION_MODIFIER = 'gravity_conversion_modifier';
    public const string GRAVITY_CYCLE_MODIFIER = 'gravity_cycle_modifier';
    public const string OXYGEN_TANK_MODIFIER = 'oxygen_tank_modifier';
    public const string PLANET_SCANNER_MODIFIER = 'planet_scanner_modifier';
    public const string LIQUID_MAP_MODIFIER = 'liquid_map_modifier';
    public const string LIQUID_MAP_MODIFIER_RANDOM_50 = 'liquid_map_modifier_random_50';
    public const string ALIEN_OIL_INCREASE_FUEL_INJECTED = 'alien_oil_increase_fuel_injected';
    public const string INVERTEBRATE_SHELL_DOUBLES_DAMAGE = 'invertebrate_shell_doubles_damage';
    public const string ROPE_MODIFIER = 'rope_modifier';

    public function load(ObjectManager $manager): void
    {
        $apronModifier = new EventModifierConfig(ModifierNameEnum::APRON_MODIFIER);

        $apronModifier
            ->setTargetEvent(StatusEvent::STATUS_APPLIED)
            ->setTagConstraints([
                PlayerStatusEnum::DIRTY => ModifierRequirementEnum::ALL_TAGS,
                ActionTypeEnum::ACTION_SUPER_DIRTY->value => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setPriority(ModifierPriorityEnum::PREVENT_EVENT)
            ->setModifierStrategy(ModifierStrategyEnum::PREVENT_EVENT)
            ->setModifierName(ModifierNameEnum::APRON_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $manager->persist($apronModifier);

        $armorModifier = new VariableEventModifierConfig('armorReduceDamage');
        $armorModifier
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTagConstraints([
                ActionEnum::HIT->value => ModifierRequirementEnum::ANY_TAGS,
                PlanetSectorEvent::FIGHT => ModifierRequirementEnum::ANY_TAGS,
                ActionOutputEnum::CRITICAL_SUCCESS => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setApplyWhenTargeted(true)
            ->setModifierRange(ModifierHolderClassEnum::TARGET_PLAYER);
        $manager->persist($armorModifier);

        $wrenchModifier = new VariableEventModifierConfig('modifier_for_player_x1.5percentageSuccess_on_roll.technician');
        $wrenchModifier
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(1.5)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setTagConstraints([ActionTypeEnum::ACTION_TECHNICIAN->value => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $manager->persist($wrenchModifier);

        $glovesModifier = new EventModifierConfig('preventClumsinessModifier');
        $glovesModifier
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setApplyWhenTargeted(true)
            ->setTagConstraints([EndCauseEnum::CLUMSINESS => ModifierRequirementEnum::ALL_TAGS])
            ->setPriority(ModifierPriorityEnum::PREVENT_EVENT)
            ->setModifierStrategy(ModifierStrategyEnum::PREVENT_EVENT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(ModifierNameEnum::GLOVES_MODIFIER);
        $manager->persist($glovesModifier);

        $soapModifier = new VariableEventModifierConfig('soapShowerActionModifier');
        $soapModifier
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([
                ActionEnum::WASH_IN_SINK->value => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::SHOWER->value => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $manager->persist($soapModifier);

        $aimModifier = new VariableEventModifierConfig('increaseShootPercentage33Percent');
        $aimModifier
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(1.33)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setTagConstraints([
                ActionTypeEnum::ACTION_SHOOT->value => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $manager->persist($aimModifier);

        $aimHunterModifier = new VariableEventModifierConfig('modifier_for_player_x1.1percentage_on_action_shoot_hunter');
        $aimHunterModifier
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(1.1)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setTagConstraints([
                ActionEnum::SHOOT_HUNTER->value => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $manager->persist($aimHunterModifier);

        $antiGravScooterModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName('modifier_for_player_+2movementPoint_on_event_action_movement_conversion')
        );
        $manager->persist($antiGravScooterModifier);

        $evenCyclesActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::CYCLE);
        $evenCyclesActivationRequirement
            ->setActivationRequirement(ModifierRequirementEnum::EVEN)
            ->buildName();
        $manager->persist($evenCyclesActivationRequirement);

        $rollingBoulderModifier = new VariableEventModifierConfig('rollingBoulderConversionModifier');
        $rollingBoulderModifier
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([ActionEnum::CONVERT_ACTION_TO_MOVEMENT->value => ModifierRequirementEnum::ALL_TAGS])
            ->addModifierRequirement($evenCyclesActivationRequirement)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $manager->persist($rollingBoulderModifier);

        $oscilloscopeSuccessModifier = new VariableEventModifierConfig('wavoscopeActionPercentageIncrease50Percents');
        $oscilloscopeSuccessModifier
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(1.5)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setTagConstraints([ActionEnum::STRENGTHEN_HULL->value => ModifierRequirementEnum::ANY_TAGS])
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $manager->persist($oscilloscopeSuccessModifier);

        $oscilloscopeSuccessModifierRenovateAction = new VariableEventModifierConfig('wavoscopeRenovateActionPercentageIncrease100Percents');
        $oscilloscopeSuccessModifierRenovateAction
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(2.0)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setTagConstraints([ActionEnum::RENOVATE->value => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $manager->persist($oscilloscopeSuccessModifierRenovateAction);

        $oscilloscopeRepairModifier = new VariableEventModifierConfig('wavoscopeRepairIncreaseBy2');
        $oscilloscopeRepairModifier
            ->setTargetVariable(DaedalusVariableEnum::HULL)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTagConstraints([ActionEnum::STRENGTHEN_HULL->value => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $manager->persist($oscilloscopeRepairModifier);

        $antennaModifier = new VariableEventModifierConfig('decreaseCommunicationActionCost1Action');
        $antennaModifier
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);
        $manager->persist($antennaModifier);

        $gravityConversionModifier = new VariableEventModifierConfig('gravityDecreaseMovementConversionGain1Movement');
        $gravityConversionModifier
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([ActionEnum::CONVERT_ACTION_TO_MOVEMENT->value => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);
        $manager->persist($gravityConversionModifier);

        $gravityCycleModifier = new VariableEventModifierConfig('gravityDecreaseMovementGainOnNewCycle');
        $gravityCycleModifier
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTagConstraints([PlayerService::BASE_PLAYER_CYCLE_CHANGE => ModifierRequirementEnum::ALL_TAGS])
            ->setApplyWhenTargeted(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);

        $manager->persist($gravityCycleModifier);

        $oxygenTankModifier = new VariableEventModifierConfig('oxygenLossReduction_oxygenTank');
        $oxygenTankModifier
            ->setTargetVariable(DaedalusVariableEnum::OXYGEN)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([EventEnum::NEW_CYCLE => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);
        $manager->persist($oxygenTankModifier);

        $planetScannerModifier = new VariableEventModifierConfig('modifier_for_daedalus_+30percentage_on_action_scan');
        $planetScannerModifier
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(30)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([ActionEnum::SCAN->value => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);
        $manager->persist($planetScannerModifier);

        $liquidMapModifier = new VariableEventModifierConfig('modifier_for_place_+1sector_revealed_on_action_scan_planet');
        $liquidMapModifier
            ->setTargetVariable(ActionVariableEnum::OUTPUT_QUANTITY)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::GET_OUTPUT_QUANTITY)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([
                ActionEnum::SCAN->value => ModifierRequirementEnum::ANY_TAGS,
                ActionOutputEnum::FAIL => ModifierRequirementEnum::NONE_TAGS,
                ModifierNameEnum::LIQUID_MAP_MODIFIER => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLACE)
            ->setModifierName(ModifierNameEnum::LIQUID_MAP_MODIFIER);
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
                ActionEnum::SCAN->value => ModifierRequirementEnum::ANY_TAGS,
                ActionOutputEnum::FAIL => ModifierRequirementEnum::NONE_TAGS,
                ModifierNameEnum::LIQUID_MAP_MODIFIER . '_random_50' => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLACE)
            ->setModifierName(ModifierNameEnum::LIQUID_MAP_MODIFIER . '_random_50')
            ->setModifierActivationRequirements([$random50]);
        $manager->persist($liquidMapRandom50Modifier);

        $alienOilIncreaseFuelInjected = new VariableEventModifierConfig('alien_oil_increase_fuel_injected');
        $alienOilIncreaseFuelInjected
            ->setTargetVariable(ActionVariableEnum::OUTPUT_QUANTITY)
            ->setDelta(4)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::GET_OUTPUT_QUANTITY)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setApplyWhenTargeted(true)
            ->setTagConstraints([
                ActionEnum::INSERT_FUEL_CHAMBER->value => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::INSERT_FUEL->value => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::EQUIPMENT);
        $manager->persist($alienOilIncreaseFuelInjected);

        $invertebrateShellDoublesDamage = new VariableEventModifierConfig('invertebrate_shell_doubles_damage');
        $invertebrateShellDoublesDamage
            ->setTargetVariable(HunterVariableEnum::HEALTH)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setApplyWhenTargeted(false)
            ->setTagConstraints([
                ActionEnum::SHOOT_HUNTER->value => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::SHOOT_RANDOM_HUNTER->value => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLACE);
        $manager->persist($invertebrateShellDoublesDamage);

        $ropeModifier = new EventModifierConfig(self::ROPE_MODIFIER);
        $ropeModifier
            ->setPriority(ModifierPriorityEnum::PREVENT_EVENT)
            ->setApplyWhenTargeted(true)
            ->setTagConstraints([
                PlanetSectorEvent::ACCIDENT => ModifierRequirementEnum::ALL_TAGS,
                PlayerVariableEnum::HEALTH_POINT => ModifierRequirementEnum::ALL_TAGS,
                PlanetSectorEnum::SEISMIC_ACTIVITY => ModifierRequirementEnum::ANY_TAGS,
                PlanetSectorEnum::MOUNTAIN => ModifierRequirementEnum::ANY_TAGS,
                PlanetSectorEnum::CAVE => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setModifierStrategy(ModifierStrategyEnum::PREVENT_EVENT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(self::ROPE_MODIFIER);
        $manager->persist($ropeModifier);

        /** @var AbstractEventConfig $eventConfigPlus1HealthPoint */
        $eventConfigPlus1HealthPoint = $this->getReference('change.variable_player_+1healthPoint');

        /** @var AbstractEventConfig $eventConfigPlus1MoralePoint */
        $eventConfigPlus1MoralePoint = $this->getReference('change.variable_player_+1moralePoint');

        /** @var AbstractEventConfig $eventConfigPlus2MovementPoint */
        $eventConfigPlus2MovementPoint = $this->getReference('change.variable_player_+2movementPoint');

        $thalassoHealthPointModifier = new TriggerEventModifierConfig('modifier_for_player_set_+1healthPoint_on_post.action_if_reason_shower');
        $thalassoHealthPointModifier
            ->setTriggeredEvent($eventConfigPlus1HealthPoint)
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setTagConstraints([
                ActionEnum::SHOWER->value => ModifierRequirementEnum::ANY_TAGS,
                ActionOutputEnum::FAIL => ModifierRequirementEnum::NONE_TAGS,
                ModifierNameEnum::THALASSO_MORALE_POINTS_MODIFIER => ModifierRequirementEnum::NONE_TAGS,
                ModifierNameEnum::THALASSO_MOVEMENT_POINTS_MODIFIER => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setApplyWhenTargeted(true)
            ->setModifierName(ModifierNameEnum::THALASSO_HEALTH_POINTS_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::EQUIPMENT);
        $manager->persist($thalassoHealthPointModifier);

        $thalassoMoralePointModifier = new TriggerEventModifierConfig('modifier_for_player_set_+1moralePoint_on_post.action_if_reason_shower');
        $thalassoMoralePointModifier
            ->setTriggeredEvent($eventConfigPlus1MoralePoint)
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setTagConstraints([
                ActionEnum::SHOWER->value => ModifierRequirementEnum::ANY_TAGS,
                ActionOutputEnum::FAIL => ModifierRequirementEnum::NONE_TAGS,
                ModifierNameEnum::THALASSO_HEALTH_POINTS_MODIFIER => ModifierRequirementEnum::NONE_TAGS,
                ModifierNameEnum::THALASSO_MOVEMENT_POINTS_MODIFIER => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setApplyWhenTargeted(true)
            ->setModifierName(ModifierNameEnum::THALASSO_MORALE_POINTS_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::EQUIPMENT);
        $manager->persist($thalassoMoralePointModifier);

        $thalassoMovementPointModifier = new TriggerEventModifierConfig('modifier_for_player_set_+2movementPoint_on_post.action_if_reason_shower');
        $thalassoMovementPointModifier
            ->setTriggeredEvent($eventConfigPlus2MovementPoint)
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setTagConstraints([
                ActionEnum::SHOWER->value => ModifierRequirementEnum::ANY_TAGS,
                ActionOutputEnum::FAIL => ModifierRequirementEnum::NONE_TAGS,
                ModifierNameEnum::THALASSO_HEALTH_POINTS_MODIFIER => ModifierRequirementEnum::NONE_TAGS,
                ModifierNameEnum::THALASSO_MORALE_POINTS_MODIFIER => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setApplyWhenTargeted(true)
            ->setModifierName(ModifierNameEnum::THALASSO_MOVEMENT_POINTS_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::EQUIPMENT);
        $manager->persist($thalassoMovementPointModifier);

        $manager->flush();

        $this->addReference(self::APRON_MODIFIER, $apronModifier);
        $this->addReference(self::ARMOR_MODIFIER, $armorModifier);
        $this->addReference(self::WRENCH_MODIFIER, $wrenchModifier);
        $this->addReference(self::GLOVES_MODIFIER, $glovesModifier);
        $this->addReference(self::SOAP_MODIFIER, $soapModifier);
        $this->addReference(self::AIM_MODIFIER, $aimModifier);
        $this->addReference(self::AIM_HUNTER_MODIFIER, $aimHunterModifier);
        $this->addReference('modifier_for_player_+2movementPoint_on_event_action_movement_conversion', $antiGravScooterModifier);
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
        $this->addReference(self::INVERTEBRATE_SHELL_DOUBLES_DAMAGE, $invertebrateShellDoublesDamage);
        $this->addReference(self::ROPE_MODIFIER, $ropeModifier);
        $this->addReference(ModifierNameEnum::THALASSO_HEALTH_POINTS_MODIFIER, $thalassoHealthPointModifier);
        $this->addReference(ModifierNameEnum::THALASSO_MORALE_POINTS_MODIFIER, $thalassoMoralePointModifier);
        $this->addReference(ModifierNameEnum::THALASSO_MOVEMENT_POINTS_MODIFIER, $thalassoMovementPointModifier);
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
