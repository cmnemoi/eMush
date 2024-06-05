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
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Event\VariableEventInterface;
use Mush\Hunter\Enum\HunterVariableEnum;
use Mush\Hunter\Event\HunterEvent;
use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\ModifierStrategyEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;

final class ProjectModifierConfigFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $trailReducerModifier = new VariableEventModifierConfig('modifier_for_daedalus_-25percentage_following_hunters_on_daedalus_travel');
        $trailReducerModifier
            ->setTargetVariable(DaedalusStatusEnum::FOLLOWING_HUNTERS)
            ->setDelta(0.75)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setTagConstraints([])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);

        $manager->persist($trailReducerModifier);
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

        $manager->persist($plasmaShieldNewCycleModifier);
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

        $manager->persist($cpuOverclock);
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
        $manager->persist($armourCorridorModifier);
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
        $manager->persist($blasterGunModifier);
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
        $manager->persist($bayDoorXXLModifier);
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

        $icarusLargerBayModifier = new VariableEventModifierConfig('modifier_for_place_+2players_allowed_on_takeoff_to_planet');
        $icarusLargerBayModifier
            ->setTargetVariable(ActionVariableEnum::OUTPUT_QUANTITY)
            ->setDelta(2)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(ActionVariableEvent::GET_OUTPUT_QUANTITY)
            ->setPriority(ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE)
            ->setTagConstraints([ActionEnum::TAKEOFF_TO_PLANET->value => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);
        $manager->persist($icarusLargerBayModifier);
        $this->addReference($icarusLargerBayModifier->getName(), $icarusLargerBayModifier);

        $manager->flush();
    }
}
