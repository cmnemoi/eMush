<?php

declare(strict_types=1);

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Chat\Enum\MessageModificationEnum;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\ConfigData\EventConfigData;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Hunter\Enum\HunterVariableEnum;
use Mush\Hunter\Event\HunterEvent;
use Mush\Modifier\ConfigData\ModifierActivationRequirementData;
use Mush\Modifier\ConfigData\ModifierConfigData;
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
use Mush\Project\Enum\ProjectName;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;

/** @codeCoverageIgnore */
final class ProjectModifierConfigFixtures extends Fixture
{
    private ObjectManager $manager;

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        $trailReducerModifier = new VariableEventModifierConfig('modifier_for_daedalus_-25percentage_following_hunters_on_daedalus_travel');
        $trailReducerModifier
            ->setTargetVariable(DaedalusStatusEnum::FOLLOWING_HUNTERS)
            ->setDelta(0.75)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setTagConstraints([])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);

        $this->manager->persist($trailReducerModifier);
        $this->addReference($trailReducerModifier->getName(), $trailReducerModifier);

        /** @var VariableEventConfig $eventConfig */
        $eventConfig = $this->getReference('change.variable_daedalus_shield_+5');
        $plasmaShieldNewCycleModifier = new TriggerEventModifierConfig('modifier_for_daedalus_+5shield_on_new_cycle');
        $plasmaShieldNewCycleModifier
            ->setTriggeredEvent($eventConfig)
            ->setTargetEvent(DaedalusCycleEvent::DAEDALUS_NEW_CYCLE)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setApplyWhenTargeted(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([]);

        $this->manager->persist($plasmaShieldNewCycleModifier);
        $this->addReference($plasmaShieldNewCycleModifier->getName(), $plasmaShieldNewCycleModifier);

        $cpuOverclock = new VariableEventModifierConfig('modifier_for_daedalus_-1actionPoint_on_action_scan_planet');
        $cpuOverclock
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([ActionEnum::SCAN->value => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);

        $this->manager->persist($cpuOverclock);
        $this->addReference($cpuOverclock->getName(), $cpuOverclock);

        $armourCorridorModifier = new VariableEventModifierConfig('modifier_for_daedalus_+1hull_on_change.variable_if_reason_hunter_shot');
        $armourCorridorModifier
            ->setTargetVariable(DaedalusVariableEnum::HULL)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([HunterEvent::HUNTER_SHOT => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);
        $this->manager->persist($armourCorridorModifier);
        $this->addReference($armourCorridorModifier->getName(), $armourCorridorModifier);

        $blasterGunModifier = new VariableEventModifierConfig('modifier_for_daedalus_-1hunter_health_on_change.variable');
        $blasterGunModifier
            ->setTargetVariable(HunterVariableEnum::HEALTH)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([
                ActionEnum::SHOOT_HUNTER_PATROL_SHIP->value => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::SHOOT_RANDOM_HUNTER_PATROL_SHIP->value => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);
        $this->manager->persist($blasterGunModifier);
        $this->addReference($blasterGunModifier->getName(), $blasterGunModifier);

        $bayDoorXXLModifier = new VariableEventModifierConfig('modifier_for_player_x1.5percentage_for_takeoff_and_land_actions');
        $bayDoorXXLModifier
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_CRITICAL)
            ->setDelta(1.5)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setTagConstraints([
                ActionEnum::TAKEOFF->value => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::LAND->value => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $this->manager->persist($bayDoorXXLModifier);
        $this->addReference($bayDoorXXLModifier->getName(), $bayDoorXXLModifier);

        $oxyMoreModifier = new VariableEventModifierConfig('modifier_for_daedalus_+1oxygen_on_change.variable_if_reason_new_cycle_random_20');
        $oxyMoreModifier
            ->setTargetVariable(DaedalusVariableEnum::OXYGEN)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints(['base_daedalus_cycle_change' => ModifierRequirementEnum::ALL_TAGS])
            ->setModifierStrategy(ModifierStrategyEnum::VARIABLE_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierName(ProjectName::OXY_MORE->value);
        $manager->persist($oxyMoreModifier);
        $this->addReference($oxyMoreModifier->getName(), $oxyMoreModifier);

        /** @var VariableEventConfig $eventConfig */
        $eventConfig = $this->getReference('change.value.max_player_+2_actionPoint');

        $noiseReducerModifier = new DirectModifierConfig('direct_modifier_player_+2_max_actionPoint');
        $noiseReducerModifier
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierStrategy(ModifierStrategyEnum::DIRECT_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);
        $manager->persist($noiseReducerModifier);
        $this->addReference($noiseReducerModifier->getName(), $noiseReducerModifier);

        $radarTransVoidModifier = VariableEventModifierConfig::fromConfigData(ModifierConfigData::getByName('modifier_for_daedalus_x2_signal_on_action_contact_sol'));
        $manager->persist($radarTransVoidModifier);
        $this->addReference($radarTransVoidModifier->getName(), $radarTransVoidModifier);

        $neronTargetingAssistModifier = new VariableEventModifierConfig('modifier_for_daedalus_x1.25_percentage_on_shoot_hunter');
        $neronTargetingAssistModifier
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(1.25)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setTagConstraints([
                ActionEnum::SHOOT_HUNTER->value => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::SHOOT_RANDOM_HUNTER->value => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::SHOOT_HUNTER_PATROL_SHIP->value => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::SHOOT_RANDOM_HUNTER_PATROL_SHIP->value => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);
        $manager->persist($neronTargetingAssistModifier);
        $this->addReference($neronTargetingAssistModifier->getName(), $neronTargetingAssistModifier);

        $floorHeatingModifier = VariableEventModifierConfig::fromConfigData(ModifierConfigData::getByName('modifier_for_daedalus_x0.5clumsiness'));
        $manager->persist($floorHeatingModifier);
        $this->addReference($floorHeatingModifier->getName(), $floorHeatingModifier);

        $hydroponicIncubatorModifier = VariableEventModifierConfig::fromConfigData(ModifierConfigData::getByName('modifier_for_place_x2_maturation_time'));
        $manager->persist($hydroponicIncubatorModifier);
        $this->addReference($hydroponicIncubatorModifier->getName(), $hydroponicIncubatorModifier);

        $icarusLavatoryModifier = new EventModifierConfig('modifier_for_player_prevent_dirty_for_exploration_finished');
        $icarusLavatoryModifier
            ->setApplyWhenTargeted(true)
            ->setTargetEvent(StatusEvent::STATUS_APPLIED)
            ->setPriority(ModifierPriorityEnum::PREVENT_EVENT)
            ->setTagConstraints([
                PlayerStatusEnum::DIRTY => ModifierRequirementEnum::ALL_TAGS,
                ExplorationEvent::EXPLORATION_FINISHED => ModifierRequirementEnum::ALL_TAGS,
            ])
            ->setModifierStrategy(ModifierStrategyEnum::PREVENT_EVENT)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER);
        $manager->persist($icarusLavatoryModifier);
        $this->addReference($icarusLavatoryModifier->getName(), $icarusLavatoryModifier);

        $icarusLargerBayModifier = new VariableEventModifierConfig('modifier_for_daedalus_+2players_allowed_on_takeoff_to_planet');
        $icarusLargerBayModifier
            ->setTargetVariable(ActionVariableEnum::OUTPUT_QUANTITY)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::GET_OUTPUT_QUANTITY)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([ActionEnum::TAKEOFF_TO_PLANET->value => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierName(ModifierNameEnum::ICARUS_LARGER_BAY_MODIFIER);
        $manager->persist($icarusLargerBayModifier);
        $this->addReference($icarusLargerBayModifier->getName(), $icarusLargerBayModifier);

        $patrolShipLauncherModifier = new VariableEventModifierConfig('modifier_for_daedalus_-1action_point_on_action_take_off');
        $patrolShipLauncherModifier
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([ActionEnum::TAKEOFF->value => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);
        $manager->persist($patrolShipLauncherModifier);
        $this->addReference($patrolShipLauncherModifier->getName(), $patrolShipLauncherModifier);

        $turretExtraFireRateModifier = new VariableEventModifierConfig('modifier_for_daedalus_x2_turret_charges_on_new_cycle');
        $turretExtraFireRateModifier
            ->setTargetVariable(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setTagConstraints([
                EquipmentEnum::TURRET_COMMAND => ModifierRequirementEnum::ALL_TAGS,
                EventEnum::NEW_CYCLE => ModifierRequirementEnum::ALL_TAGS,
                VariableEventInterface::GAIN => ModifierRequirementEnum::ALL_TAGS,
            ])
            ->setModifierStrategy(ModifierStrategyEnum::VARIABLE_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);
        $manager->persist($turretExtraFireRateModifier);
        $this->addReference($turretExtraFireRateModifier->getName(), $turretExtraFireRateModifier);

        $quantumSensorModifier = new VariableEventModifierConfig('modifier_for_daedalus_+1sector_revealed_on_action_analyze_planet');
        $quantumSensorModifier
            ->setTargetVariable(ActionVariableEnum::OUTPUT_QUANTITY)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::GET_OUTPUT_QUANTITY)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([
                ActionEnum::ANALYZE_PLANET->value => ModifierRequirementEnum::ANY_TAGS,
                ActionOutputEnum::FAIL => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setModifierStrategy(ModifierStrategyEnum::VARIABLE_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);
        $manager->persist($quantumSensorModifier);
        $this->addReference($quantumSensorModifier->getName(), $quantumSensorModifier);

        /** @var VariableEventConfig $eventConfig */
        $eventConfig = $this->getReference('change.variable_patrol_ship_max_charges_+6');
        $patrolShipRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::HOLDER_NAME);
        $patrolShipRequirement
            ->setActivationRequirement(EquipmentEnum::PATROL_SHIP)
            ->buildName();
        $this->manager->persist($patrolShipRequirement);

        $maxChargesPatrolShipExtraAmmoModifier = new DirectModifierConfig('modifier_for_daedalus_+6_patrol_ship_max_charges');
        $maxChargesPatrolShipExtraAmmoModifier
            ->setTriggeredEvent($eventConfig)
            ->setEventActivationRequirements([
                $patrolShipRequirement,
            ])
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);

        $this->manager->persist($maxChargesPatrolShipExtraAmmoModifier);
        $this->addReference($maxChargesPatrolShipExtraAmmoModifier->getName(), $maxChargesPatrolShipExtraAmmoModifier);

        /** @var VariableEventConfig $eventConfig */
        $eventConfig = $this->getReference('change.variable_patrol_ship_set_charges_to_maximum');

        $maxChargesPatrolShipExtraAmmoModifier = new DirectModifierConfig('modifier_for_daedalus_patrol_ship_set_charges_to_maximum');
        $maxChargesPatrolShipExtraAmmoModifier
            ->setTriggeredEvent($eventConfig)
            ->setEventActivationRequirements([
                $patrolShipRequirement,
            ])->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);
        $this->manager->persist($maxChargesPatrolShipExtraAmmoModifier);
        $this->addReference($maxChargesPatrolShipExtraAmmoModifier->getName(), $maxChargesPatrolShipExtraAmmoModifier);

        $patulineScramblerModifier = EventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(MessageModificationEnum::PATULINE_SCRAMBLER_MODIFICATION)
        );
        $this->manager->persist($patulineScramblerModifier);
        $this->addReference($patulineScramblerModifier->getName(), $patulineScramblerModifier);

        /** @var VariableEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigData::CHANGE_VALUE_PLUS_1_MAX_PLAYER_SPORE);
        $playerIsNotMushRequirement = ModifierActivationRequirement::fromConfigData(
            ModifierActivationRequirementData::getByName(ModifierRequirementEnum::PLAYER_IS_NOT_MUSH)
        );
        $this->manager->persist($playerIsNotMushRequirement);
        $this->addReference(ModifierRequirementEnum::PLAYER_IS_NOT_MUSH, $playerIsNotMushRequirement);

        $mushovoreBacteriaModifier = DirectModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_PLUS_1_MAX_SPORES)
        );
        $mushovoreBacteriaModifier
            ->setTriggeredEvent($eventConfig)
            ->addEventActivationRequirement($playerIsNotMushRequirement);
        $this->manager->persist($mushovoreBacteriaModifier);
        $this->addReference($mushovoreBacteriaModifier->getName(), $mushovoreBacteriaModifier);

        /** @var VariableEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigData::CHANGE_VALUE_MINUS_2_MAX_DAEDALUS_SPORES);
        $antisporeGasModifier = DirectModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::DAEDALUS_MINUS_2_MAX_SPORES)
        );
        $antisporeGasModifier->setTriggeredEvent($eventConfig);
        $this->manager->persist($antisporeGasModifier);
        $this->addReference($antisporeGasModifier->getName(), $antisporeGasModifier);

        $ultraHealingPomadeModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLUS_1_HEALTH_POINTS_ON_HEAL)
        );
        $this->manager->persist($ultraHealingPomadeModifier);
        $this->addReference($ultraHealingPomadeModifier->getName(), $ultraHealingPomadeModifier);

        /** @var VariableEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigData::CHANGE_VARIABLE_TURRET_MAX_CHARGE_4);

        $holderNameTurretActivationRequirement = ModifierActivationRequirement::fromConfigData(
            ModifierActivationRequirementData::getByName('holder_name_turret')
        );
        $this->manager->persist($holderNameTurretActivationRequirement);
        $this->addReference($holderNameTurretActivationRequirement->getName(), $holderNameTurretActivationRequirement);

        $teslaSup2XMaxChargesModifier = DirectModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::TURRET_MAX_CHARGES_PLUS_4)
        );
        $teslaSup2XMaxChargesModifier
            ->setTriggeredEvent($eventConfig)
            ->addEventActivationRequirement($holderNameTurretActivationRequirement);
        $this->manager->persist($teslaSup2XMaxChargesModifier);
        $this->addReference($teslaSup2XMaxChargesModifier->getName(), $teslaSup2XMaxChargesModifier);

        /** @var VariableEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigData::CHANGE_VARIABLE_TURRET_CHARGE_8);

        $teslaSup2XChargesModifier = DirectModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::TURRET_CHARGES_PLUS_8)
        );
        $teslaSup2XChargesModifier
            ->setTriggeredEvent($eventConfig);
        $this->manager->persist($teslaSup2XChargesModifier);
        $this->addReference($teslaSup2XChargesModifier->getName(), $teslaSup2XChargesModifier);

        $constipasporeSerumModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLUS_2_ACTION_POINTS_ON_EXTRACT_SPORE)
        );
        $this->manager->persist($constipasporeSerumModifier);
        $this->addReference($constipasporeSerumModifier->getName(), $constipasporeSerumModifier);

        $guaranaCappuccinoModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_PLUS_1_ACTION_POINTS_ON_CONSUME_ACTION_IF_COFFEE)
        );
        $this->manager->persist($guaranaCappuccinoModifier);
        $this->addReference($guaranaCappuccinoModifier->getName(), $guaranaCappuccinoModifier);

        /** @var VariableEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigData::CHANGE_VALUE_PLUS_2_MAX_PRIVATE_CHANNELS);

        $EridianiMaxPrivateChannelsModifier = DirectModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::ERIDIANI_PLUS_2_MAX_PRIVATE_CHANNELS)
        );
        $EridianiMaxPrivateChannelsModifier->setTriggeredEvent($eventConfig);
        $this->addReference($EridianiMaxPrivateChannelsModifier->getName(), $EridianiMaxPrivateChannelsModifier);
        $this->manager->persist($EridianiMaxPrivateChannelsModifier);

        $this->manager->flush();
    }
}
