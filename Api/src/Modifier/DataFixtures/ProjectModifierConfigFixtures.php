<?php

declare(strict_types=1);

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Hunter\Enum\HunterVariableEnum;
use Mush\Hunter\Event\HunterEvent;
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
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;

/** @codeCoverageIgnore */
final class ProjectModifierConfigFixtures extends Fixture
{
    private ObjectManager $manager;
    private array $patrolShipExtraAmmoModifierActivationRequirements = [];

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        $this->createPatrolShipExtraAmmoModifierActivationRequirements();

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
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);

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

        $radarTransVoidModifier = new VariableEventModifierConfig('modifier_for_daedalus_x2_signal_on_action_contact_sol');
        $radarTransVoidModifier
            ->setTargetVariable(ActionVariableEnum::OUTPUT_QUANTITY)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(ActionVariableEvent::GET_OUTPUT_QUANTITY)
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setTagConstraints([ActionEnum::CONTACT_SOL->value => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);
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

        $maxChargesPatrolshipExtraAmmoModifierForAlphaTamarin = new DirectModifierConfig('modifier_for_daedalus_+6_patrol_ship_alpha_tamarin_max_charges');
        $maxChargesPatrolshipExtraAmmoModifierForAlphaTamarin
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN],
            ]);
        $this->manager->persist($maxChargesPatrolshipExtraAmmoModifierForAlphaTamarin);
        $this->addReference($maxChargesPatrolshipExtraAmmoModifierForAlphaTamarin->getName(), $maxChargesPatrolshipExtraAmmoModifierForAlphaTamarin);

        $maxChargesPatrolshipExtraAmmoModifierForAlphaLongane = new DirectModifierConfig('modifier_for_daedalus_+6_patrol_ship_alpha_longane_max_charges');
        $maxChargesPatrolshipExtraAmmoModifierForAlphaLongane
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_ALPHA_LONGANE],
            ]);
        $this->manager->persist($maxChargesPatrolshipExtraAmmoModifierForAlphaLongane);
        $this->addReference($maxChargesPatrolshipExtraAmmoModifierForAlphaLongane->getName(), $maxChargesPatrolshipExtraAmmoModifierForAlphaLongane);

        $maxChargesPatrolshipExtraAmmoModifierForAlphaJujube = new DirectModifierConfig('modifier_for_daedalus_+6_patrol_ship_alpha_jujube_max_charges');
        $maxChargesPatrolshipExtraAmmoModifierForAlphaJujube
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_ALPHA_JUJUBE],
            ]);
        $this->manager->persist($maxChargesPatrolshipExtraAmmoModifierForAlphaJujube);
        $this->addReference($maxChargesPatrolshipExtraAmmoModifierForAlphaJujube->getName(), $maxChargesPatrolshipExtraAmmoModifierForAlphaJujube);

        $maxChargesPatrolshipExtraAmmoModifierForBravoPlanton = new DirectModifierConfig('modifier_for_daedalus_+6_patrol_ship_bravo_planton_max_charges');
        $maxChargesPatrolshipExtraAmmoModifierForBravoPlanton
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_BRAVO_PLANTON],
            ]);
        $this->manager->persist($maxChargesPatrolshipExtraAmmoModifierForBravoPlanton);
        $this->addReference($maxChargesPatrolshipExtraAmmoModifierForBravoPlanton->getName(), $maxChargesPatrolshipExtraAmmoModifierForBravoPlanton);

        /** @var VariableEventConfig $eventConfig */
        $eventConfig = $this->getReference('change.variable_patrol_ship_set_charges_to_maximum');

        $maxChargesPatrolshipExtraAmmoModifierForBravoSocrate = new DirectModifierConfig('modifier_for_daedalus_+6_patrol_ship_bravo_socrate_max_charges');
        $maxChargesPatrolshipExtraAmmoModifierForBravoSocrate
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_BRAVO_SOCRATE],
            ]);
        $this->manager->persist($maxChargesPatrolshipExtraAmmoModifierForBravoSocrate);
        $this->addReference($maxChargesPatrolshipExtraAmmoModifierForBravoSocrate->getName(), $maxChargesPatrolshipExtraAmmoModifierForBravoSocrate);

        $maxChargesPatrolshipExtraAmmoModifierForBravoEpicure = new DirectModifierConfig('modifier_for_daedalus_+6_patrol_ship_bravo_epicure_max_charges');
        $maxChargesPatrolshipExtraAmmoModifierForBravoEpicure
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_BRAVO_EPICURE],
            ]);
        $this->manager->persist($maxChargesPatrolshipExtraAmmoModifierForBravoEpicure);
        $this->addReference($maxChargesPatrolshipExtraAmmoModifierForBravoEpicure->getName(), $maxChargesPatrolshipExtraAmmoModifierForBravoEpicure);

        $maxChargesPatrolshipExtraAmmoModifierForAlpha2Wallis = new DirectModifierConfig('modifier_for_daedalus_+6_patrol_ship_alpha_2_wallis_max_charges');
        $maxChargesPatrolshipExtraAmmoModifierForAlpha2Wallis
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS],
            ]);
        $this->manager->persist($maxChargesPatrolshipExtraAmmoModifierForAlpha2Wallis);
        $this->addReference($maxChargesPatrolshipExtraAmmoModifierForAlpha2Wallis->getName(), $maxChargesPatrolshipExtraAmmoModifierForAlpha2Wallis);

        $maxChargesPatrolshipExtraAmmoModifierForAlphaTamarin = new DirectModifierConfig('modifier_for_daedalus_patrol_ship_alpha_tamarin_set_charges_to_maximum');
        $maxChargesPatrolshipExtraAmmoModifierForAlphaTamarin
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN],
            ]);
        $this->manager->persist($maxChargesPatrolshipExtraAmmoModifierForAlphaTamarin);
        $this->addReference($maxChargesPatrolshipExtraAmmoModifierForAlphaTamarin->getName(), $maxChargesPatrolshipExtraAmmoModifierForAlphaTamarin);

        $setChargesToMaximumPatrolshipExtraAmmoModifierForAlphaLongane = new DirectModifierConfig('modifier_for_daedalus_patrol_ship_alpha_longane_set_charges_to_maximum');
        $setChargesToMaximumPatrolshipExtraAmmoModifierForAlphaLongane
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_ALPHA_LONGANE],
            ]);
        $this->manager->persist($setChargesToMaximumPatrolshipExtraAmmoModifierForAlphaLongane);
        $this->addReference($setChargesToMaximumPatrolshipExtraAmmoModifierForAlphaLongane->getName(), $setChargesToMaximumPatrolshipExtraAmmoModifierForAlphaLongane);

        $setChargesToMaximumPatrolshipExtraAmmoModifierForAlphaJujube = new DirectModifierConfig('modifier_for_daedalus_patrol_ship_alpha_jujube_set_charges_to_maximum');
        $setChargesToMaximumPatrolshipExtraAmmoModifierForAlphaJujube
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_ALPHA_JUJUBE],
            ]);
        $this->manager->persist($setChargesToMaximumPatrolshipExtraAmmoModifierForAlphaJujube);
        $this->addReference($setChargesToMaximumPatrolshipExtraAmmoModifierForAlphaJujube->getName(), $setChargesToMaximumPatrolshipExtraAmmoModifierForAlphaJujube);

        $setChargesToMaximumPatrolshipExtraAmmoModifierForBravoPlanton = new DirectModifierConfig('modifier_for_daedalus_patrol_ship_bravo_planton_set_charges_to_maximum');
        $setChargesToMaximumPatrolshipExtraAmmoModifierForBravoPlanton
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_BRAVO_PLANTON],
            ]);
        $this->manager->persist($setChargesToMaximumPatrolshipExtraAmmoModifierForBravoPlanton);
        $this->addReference($setChargesToMaximumPatrolshipExtraAmmoModifierForBravoPlanton->getName(), $setChargesToMaximumPatrolshipExtraAmmoModifierForBravoPlanton);

        $setChargesToMaximumPatrolshipExtraAmmoModifierForBravoSocrate = new DirectModifierConfig('modifier_for_daedalus_patrol_ship_bravo_socrate_set_charges_to_maximum');
        $setChargesToMaximumPatrolshipExtraAmmoModifierForBravoSocrate
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_BRAVO_SOCRATE],
            ]);
        $this->manager->persist($setChargesToMaximumPatrolshipExtraAmmoModifierForBravoSocrate);
        $this->addReference($setChargesToMaximumPatrolshipExtraAmmoModifierForBravoSocrate->getName(), $setChargesToMaximumPatrolshipExtraAmmoModifierForBravoSocrate);

        $setChargesToMaximumPatrolshipExtraAmmoModifierForBravoEpicure = new DirectModifierConfig('modifier_for_daedalus_patrol_ship_bravo_epicure_set_charges_to_maximum');
        $setChargesToMaximumPatrolshipExtraAmmoModifierForBravoEpicure
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_BRAVO_EPICURE],
            ]);
        $this->manager->persist($setChargesToMaximumPatrolshipExtraAmmoModifierForBravoEpicure);
        $this->addReference($setChargesToMaximumPatrolshipExtraAmmoModifierForBravoEpicure->getName(), $setChargesToMaximumPatrolshipExtraAmmoModifierForBravoEpicure);

        $setChargesToMaximumPatrolshipExtraAmmoModifierForAlpha2Wallis = new DirectModifierConfig('modifier_for_daedalus_patrol_ship_alpha_2_wallis_set_charges_to_maximum');
        $setChargesToMaximumPatrolshipExtraAmmoModifierForAlpha2Wallis
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS],
            ]);
        $this->manager->persist($setChargesToMaximumPatrolshipExtraAmmoModifierForAlpha2Wallis);
        $this->addReference($setChargesToMaximumPatrolshipExtraAmmoModifierForAlpha2Wallis->getName(), $setChargesToMaximumPatrolshipExtraAmmoModifierForAlpha2Wallis);

        $this->manager->flush();
    }

    private function createPatrolShipExtraAmmoModifierActivationRequirements(): void
    {
        $tamarin = new ModifierActivationRequirement(ModifierRequirementEnum::HOLDER_NAME);
        $tamarin
            ->setActivationRequirement(EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN)
            ->buildName();
        $this->manager->persist($tamarin);
        $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN] = $tamarin;

        $longane = new ModifierActivationRequirement(ModifierRequirementEnum::HOLDER_NAME);
        $longane
            ->setActivationRequirement(EquipmentEnum::PATROL_SHIP_ALPHA_LONGANE)
            ->buildName();
        $this->manager->persist($longane);
        $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_ALPHA_LONGANE] = $longane;

        $jujube = new ModifierActivationRequirement(ModifierRequirementEnum::HOLDER_NAME);
        $jujube
            ->setActivationRequirement(EquipmentEnum::PATROL_SHIP_ALPHA_JUJUBE)
            ->buildName();
        $this->manager->persist($jujube);
        $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_ALPHA_JUJUBE] = $jujube;

        $socrate = new ModifierActivationRequirement(ModifierRequirementEnum::HOLDER_NAME);
        $socrate
            ->setActivationRequirement(EquipmentEnum::PATROL_SHIP_BRAVO_SOCRATE)
            ->buildName();
        $this->manager->persist($socrate);
        $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_BRAVO_SOCRATE] = $socrate;

        $epicure = new ModifierActivationRequirement(ModifierRequirementEnum::HOLDER_NAME);
        $epicure
            ->setActivationRequirement(EquipmentEnum::PATROL_SHIP_BRAVO_EPICURE)
            ->buildName();
        $this->manager->persist($epicure);
        $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_BRAVO_EPICURE] = $epicure;

        $planton = new ModifierActivationRequirement(ModifierRequirementEnum::HOLDER_NAME);
        $planton
            ->setActivationRequirement(EquipmentEnum::PATROL_SHIP_BRAVO_PLANTON)
            ->buildName();
        $this->manager->persist($planton);
        $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_BRAVO_PLANTON] = $planton;

        $wallis = new ModifierActivationRequirement(ModifierRequirementEnum::HOLDER_NAME);
        $wallis
            ->setActivationRequirement(EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS)
            ->buildName();
        $this->manager->persist($wallis);
        $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS] = $wallis;
    }
}
