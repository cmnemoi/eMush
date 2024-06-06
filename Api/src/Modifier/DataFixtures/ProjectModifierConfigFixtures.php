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
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Event\VariableEventInterface;
use Mush\Hunter\Enum\HunterVariableEnum;
use Mush\Hunter\Event\HunterEvent;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Enum\DaedalusStatusEnum;

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

        /** @var VariableEventConfig $eventConfig */
        $eventConfig = $this->getReference('change.variable_patrol_ship_max_charges_+6');

        $patrolshipExtraAmmoModifierForAlphaTamarin = new DirectModifierConfig('modifier_for_daedalus_+6_patrol_ship_alpha_tamarin_max_charges');
        $patrolshipExtraAmmoModifierForAlphaTamarin
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN],
            ]);
        $this->manager->persist($patrolshipExtraAmmoModifierForAlphaTamarin);
        $this->addReference($patrolshipExtraAmmoModifierForAlphaTamarin->getName(), $patrolshipExtraAmmoModifierForAlphaTamarin);

        $patrolshipExtraAmmoModifierForAlphaLongane = new DirectModifierConfig('modifier_for_daedalus_+6_patrol_ship_alpha_longane_max_charges');
        $patrolshipExtraAmmoModifierForAlphaLongane
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_ALPHA_LONGANE],
            ]);
        $this->manager->persist($patrolshipExtraAmmoModifierForAlphaLongane);
        $this->addReference($patrolshipExtraAmmoModifierForAlphaLongane->getName(), $patrolshipExtraAmmoModifierForAlphaLongane);

        $patrolshipExtraAmmoModifierForAlphaJujube = new DirectModifierConfig('modifier_for_daedalus_+6_patrol_ship_alpha_jujube_max_charges');
        $patrolshipExtraAmmoModifierForAlphaJujube
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_ALPHA_JUJUBE],
            ]);
        $this->manager->persist($patrolshipExtraAmmoModifierForAlphaJujube);
        $this->addReference($patrolshipExtraAmmoModifierForAlphaJujube->getName(), $patrolshipExtraAmmoModifierForAlphaJujube);

        $patrolshipExtraAmmoModifierForBravoPlanton = new DirectModifierConfig('modifier_for_daedalus_+6_patrol_ship_bravo_planton_max_charges');
        $patrolshipExtraAmmoModifierForBravoPlanton
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_BRAVO_PLANTON],
            ]);
        $this->manager->persist($patrolshipExtraAmmoModifierForBravoPlanton);
        $this->addReference($patrolshipExtraAmmoModifierForBravoPlanton->getName(), $patrolshipExtraAmmoModifierForBravoPlanton);

        $patrolshipExtraAmmoModifierForBravoSocrate = new DirectModifierConfig('modifier_for_daedalus_+6_patrol_ship_bravo_socrate_max_charges');
        $patrolshipExtraAmmoModifierForBravoSocrate
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_BRAVO_SOCRATE],
            ]);
        $this->manager->persist($patrolshipExtraAmmoModifierForBravoSocrate);
        $this->addReference($patrolshipExtraAmmoModifierForBravoSocrate->getName(), $patrolshipExtraAmmoModifierForBravoSocrate);

        $patrolshipExtraAmmoModifierForBravoEpicure = new DirectModifierConfig('modifier_for_daedalus_+6_patrol_ship_bravo_epicure_max_charges');
        $patrolshipExtraAmmoModifierForBravoEpicure
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_BRAVO_EPICURE],
            ]);
        $this->manager->persist($patrolshipExtraAmmoModifierForBravoEpicure);
        $this->addReference($patrolshipExtraAmmoModifierForBravoEpicure->getName(), $patrolshipExtraAmmoModifierForBravoEpicure);

        $patrolshipExtraAmmoModifierForAlpha2Wallis = new DirectModifierConfig('modifier_for_daedalus_+6_patrol_ship_alpha_2_wallis_max_charges');
        $patrolshipExtraAmmoModifierForAlpha2Wallis
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setModifierActivationRequirements([
                $this->patrolShipExtraAmmoModifierActivationRequirements[EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS],
            ]);
        $this->manager->persist($patrolshipExtraAmmoModifierForAlpha2Wallis);
        $this->addReference($patrolshipExtraAmmoModifierForAlpha2Wallis->getName(), $patrolshipExtraAmmoModifierForAlpha2Wallis);

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
