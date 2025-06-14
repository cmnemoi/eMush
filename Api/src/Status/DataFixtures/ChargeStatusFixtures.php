<?php

namespace Mush\Status\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\DataFixtures\GearModifierConfigFixtures;
use Mush\Modifier\DataFixtures\StatusModifierConfigFixtures;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Status\ConfigData\StatusConfigData;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\HunterStatusEnum;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;

class ChargeStatusFixtures extends Fixture implements DependentFixtureInterface
{
    public const SCOOTER_CHARGE = 'scooter_charge';
    public const MICROWAVE_CHARGE = 'microwave_charge';
    public const BLASTER_CHARGE = 'blaster_charge';
    public const OLDFAITHFUL_CHARGE = 'oldFaithful_charge';
    public const BIG_WEAPON_CHARGE = 'big_weapon_charge';
    public const COFFEE_CHARGE = 'coffee_machine_charge';
    public const DISPENSER_CHARGE = 'dispenser_charge';
    public const TURRET_CHARGE = 'turret_charge';
    public const PATROLLER_CHARGE = 'patroller_charge';
    public const SINK_CHARGE = 'sink_charge';

    public const FIRE_STATUS = 'fire_status';
    public const PLANT_YOUNG = 'plant_young';
    public const EUREKA_MOMENT = 'eureka_moment';
    public const FIRST_TIME = 'first_time';
    public const MUSH_STATUS = 'mush_status';
    public const CONTAMINATED_FOOD = 'contaminated_food';
    public const DRUG_EATEN_STATUS = 'drug_eaten_status';
    public const DID_THE_THING_STATUS = 'did_the_thing_status';
    public const DID_BORING_SPEECH_STATUS = 'did_boring_speech_status';
    public const ALREADY_WASHED_IN_THE_SINK = 'already_washed_in_the_sink';
    public const ASTEROID_TRUCE_CYCLES = 'asteroid_truce_cycles';
    public const HAS_REJUVENATED = 'has_rejuvenated';
    public const PATROL_SHIP_ARMOR = 'patrol_ship_armor';
    public const PASIPHAE_ARMOR = 'pasiphae_armor';
    public const TRAVELING_STATUS = 'traveling_status';
    public const EXPLORATION_OXYGEN_STATUS = 'exploration_oxygen_status';
    public const EXPLORATION_FUEL_STATUS = 'exploration_fuel_status';
    public const HUNTER_TRUCE_CYCLES = 'hunter_truce_cycles';
    public const FOLLOWING_HUNTERS_STATUS = 'following_hunters_status';
    public const CHANGED_CPU_PRIORITY = 'changed_cpu_priority';
    public const COFFEE_THERMOS_CHARGE = 'coffee_thermos_charge';
    public const LUNCHBOX_CHARGE = 'lunchbox_charge';

    public const UPDATING_TRACKIE_STATUS = 'updating_trackie_status';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setStatusName(StatusEnum::ATTEMPT)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::NONE)
            ->setStartCharge(0)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($attemptConfig);

        $microwaveCharge = new ChargeStatusConfig();
        $microwaveCharge
            ->setStatusName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setMaxCharge(4)
            ->setStartCharge(1)
            ->setDischargeStrategies([ActionEnum::EXPRESS_COOK->value])
            ->buildName(GameConfigEnum::DEFAULT, ToolItemEnum::MICROWAVE);
        $manager->persist($microwaveCharge);

        $scooterCharge = new ChargeStatusConfig();
        $scooterCharge
            ->setStatusName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setMaxCharge(8)
            ->setStartCharge(2)
            ->setDischargeStrategies([ModifierNameEnum::ANTIGRAV_SCOOTER_CONVERSION_MODIFIER])
            ->buildName(GameConfigEnum::DEFAULT, GearItemEnum::ANTIGRAV_SCOOTER);
        $manager->persist($scooterCharge);

        $blasterCharge = new ChargeStatusConfig();
        $blasterCharge
            ->setStatusName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setMaxCharge(3)
            ->setStartCharge(1)
            ->setDischargeStrategies([ActionEnum::SHOOT->value, ActionEnum::SHOOT_CAT->value])
            ->buildName(GameConfigEnum::DEFAULT, ItemEnum::BLASTER);
        $manager->persist($blasterCharge);

        $oldFaithfulCharge = new ChargeStatusConfig();
        $oldFaithfulCharge
            ->setStatusName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setMaxCharge(12)
            ->setStartCharge(12)
            ->setDischargeStrategies([ActionEnum::SHOOT->value])
            ->buildName(GameConfigEnum::DEFAULT, ItemEnum::OLD_FAITHFUL);
        $manager->persist($oldFaithfulCharge);

        $bigWeaponCharge = new ChargeStatusConfig();
        $bigWeaponCharge
            ->setStatusName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setMaxCharge(1)
            ->setStartCharge(1)
            ->setDischargeStrategies([ActionEnum::SHOOT->value])
            ->buildName(GameConfigEnum::DEFAULT, ItemEnum::ROCKET_LAUNCHER);
        $manager->persist($bigWeaponCharge);

        $dispenserCharge = new ChargeStatusConfig();
        $dispenserCharge
            ->setStatusName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_INCREMENT)
            ->setMaxCharge(1)
            ->setStartCharge(1)
            ->setDischargeStrategies([ActionEnum::DISPENSE->value])
            ->buildName(GameConfigEnum::DEFAULT, EquipmentEnum::NARCOTIC_DISTILLER);
        $manager->persist($dispenserCharge);

        $coffeeCharge = new ChargeStatusConfig();
        $coffeeCharge
            ->setStatusName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::COFFEE_MACHINE_CHARGE_INCREMENT)
            ->setMaxCharge(1)
            ->setStartCharge(1)
            ->setDischargeStrategies([ActionEnum::COFFEE->value])
            ->buildName(GameConfigEnum::DEFAULT, EquipmentEnum::COFFEE_MACHINE);
        $manager->persist($coffeeCharge);

        $turretCharge = new ChargeStatusConfig();
        $turretCharge
            ->setStatusName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setMaxCharge(4)
            ->setStartCharge(4)
            ->setDischargeStrategies([ActionEnum::SHOOT_HUNTER->value, ActionEnum::SHOOT_RANDOM_HUNTER->value])
            ->buildName(GameConfigEnum::DEFAULT, EquipmentEnum::TURRET_COMMAND);
        $manager->persist($turretCharge);

        $patrolShipCharge = new ChargeStatusConfig();
        $patrolShipCharge
            ->setStatusName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::PATROL_SHIP_CHARGE_INCREMENT)
            ->setMaxCharge(10)
            ->setStartCharge(10)
            ->buildName(GameConfigEnum::DEFAULT, EquipmentEnum::PATROL_SHIP);
        $manager->persist($patrolShipCharge);

        $sinkCharge = new ChargeStatusConfig();
        $sinkCharge
            ->setStatusName(EquipmentStatusEnum::SINK_CHARGE)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_INCREMENT)
            ->setMaxCharge(1)
            ->setStartCharge(1)
            ->setDischargeStrategies([ActionEnum::WASH_IN_SINK->value])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($sinkCharge);

        /** @var ActionConfig $reportFireAction */
        $reportFireAction = $this->getReference(ActionsFixtures::REPORT_FIRE);
        $fireStatus = new ChargeStatusConfig();
        $fireStatus
            ->setStatusName(StatusEnum::FIRE)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setActionConfigs([$reportFireAction])
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($fireStatus);

        $plantYoung = new ChargeStatusConfig();
        $plantYoung
            ->setStatusName(EquipmentStatusEnum::PLANT_YOUNG)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::GROWING_PLANT)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($plantYoung);

        $eurekaMoment = new ChargeStatusConfig();
        $eurekaMoment
            ->setStatusName(PlayerStatusEnum::EUREKA_MOMENT)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setAutoRemove(true)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($eurekaMoment);

        $firstTime = new ChargeStatusConfig();
        $firstTime
            ->setStatusName(PlayerStatusEnum::FIRST_TIME)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setStartCharge(1)
            ->setAutoRemove(true)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($firstTime);

        /** @var VariableEventModifierConfig $consumeSatietyModifier */
        $consumeSatietyModifier = $this->getReference(StatusModifierConfigFixtures::MUSH_CONSUME_SATIETY_MODIFIER);

        /** @var VariableEventModifierConfig $consumeModifier */
        $consumeModifier = $this->getReference(StatusModifierConfigFixtures::MUSH_CONSUME_MODIFIER);

        /** @var VariableEventModifierConfig $moraleModifier */
        $moraleModifier = $this->getReference(StatusModifierConfigFixtures::MUSH_MORALE_MODIFIER);

        /** @var ActionConfig $trapClosetAction */
        $trapClosetAction = $this->getReference(ActionEnum::TRAP_CLOSET->value);

        $mushStatus = new ChargeStatusConfig();
        $mushStatus
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT_RESET)
            ->setMaxCharge(1)
            ->setStartCharge(0)
            ->setModifierConfigs([
                $consumeModifier,
                $consumeSatietyModifier,
                $moraleModifier,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($mushStatus);

        $contaminated = new ChargeStatusConfig();
        $contaminated
            ->setStatusName(EquipmentStatusEnum::CONTAMINATED)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setChargeVisibility(VisibilityEnum::MUSH)
            ->setStartCharge(1)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($contaminated);

        $drug_eaten = new ChargeStatusConfig();
        $drug_eaten
            ->setStatusName(PlayerStatusEnum::DRUG_EATEN)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_DECREMENT)
            ->setStartCharge(1)
            ->setAutoRemove(true)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($drug_eaten);

        $did_the_thing = new ChargeStatusConfig();
        $did_the_thing
            ->setStatusName(PlayerStatusEnum::DID_THE_THING)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
            ->setStartCharge(1)
            ->setAutoRemove(true)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($did_the_thing);

        $did_boring_speech = new ChargeStatusConfig();
        $did_boring_speech
            ->setStatusName(PlayerStatusEnum::DID_BORING_SPEECH)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
            ->setStartCharge(1)
            ->setAutoRemove(true)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($did_boring_speech);

        $already_washed_in_the_sink = new ChargeStatusConfig();
        $already_washed_in_the_sink
            ->setStatusName(PlayerStatusEnum::ALREADY_WASHED_IN_THE_SINK)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
            ->setStartCharge(1)
            ->setAutoRemove(true)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($already_washed_in_the_sink);

        $updatingTrackie = new ChargeStatusConfig();
        $updatingTrackie
            ->setStatusName(EquipmentStatusEnum::UPDATING)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setStartCharge(4)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_DECREMENT)
            ->setAutoRemove(true)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($updatingTrackie);

        $asteroidTruceCycles = new ChargeStatusConfig();
        $asteroidTruceCycles
            ->setStatusName(HunterStatusEnum::ASTEROID_TRUCE_CYCLES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setStartCharge(6)
            ->setMaxCharge(6)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_DECREMENT)
            ->setAutoRemove(true)
            ->buildName(GameConfigEnum::DEFAULT, HunterEnum::ASTEROID);
        $manager->persist($asteroidTruceCycles);

        $rejuvenationCharge = new ChargeStatusConfig();
        $rejuvenationCharge
            ->setStatusName(PlayerStatusEnum::HAS_REJUVENATED)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setStartCharge(1)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
            ->setAutoRemove(true)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($rejuvenationCharge);

        $patrolShipArmor = new ChargeStatusConfig();
        $patrolShipArmor
            ->setStatusName(EquipmentStatusEnum::PATROL_SHIP_ARMOR)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setStartCharge(10)
            ->setMaxCharge(10)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($patrolShipArmor);

        $pasiphaeArmor = new ChargeStatusConfig();
        $pasiphaeArmor
            ->setStatusName(EquipmentStatusEnum::PATROL_SHIP_ARMOR)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setStartCharge(12)
            ->setMaxCharge(12)
            ->buildName(GameConfigEnum::DEFAULT, EquipmentEnum::PASIPHAE);
        $manager->persist($pasiphaeArmor);

        $traveling = new ChargeStatusConfig();
        $traveling
            ->setStatusName(DaedalusStatusEnum::TRAVELING)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setStartCharge(1)
            ->setMaxCharge(1)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_DECREMENT)
            ->setAutoRemove(true)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($traveling);

        /** @var VariableEventModifierConfig $gravityConversionModifier */
        $gravityConversionModifier = $this->getReference(GearModifierConfigFixtures::GRAVITY_CONVERSION_MODIFIER);

        /** @var VariableEventModifierConfig $gravityCycleModifier */
        $gravityCycleModifier = $this->getReference(GearModifierConfigFixtures::GRAVITY_CYCLE_MODIFIER);
        $noGravityRepaired = new ChargeStatusConfig();
        $noGravityRepaired
            ->setStatusName(DaedalusStatusEnum::NO_GRAVITY_REPAIRED)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setStartCharge(1)
            ->setMaxCharge(1)
            ->setAutoRemove(true)
            ->setModifierConfigs([
                $gravityConversionModifier,
                $gravityCycleModifier,
            ])
            ->buildName(GameConfigEnum::TEST);
        $manager->persist($noGravityRepaired);

        $explorationOxygenStatus = new ChargeStatusConfig();
        $explorationOxygenStatus
            ->setStatusName(DaedalusStatusEnum::EXPLORATION_OXYGEN)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setStartCharge(0)
            ->setMaxCharge(24)
            ->setAutoRemove(false)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($explorationOxygenStatus);

        $explorationFuelStatus = new ChargeStatusConfig();
        $explorationFuelStatus
            ->setStatusName(DaedalusStatusEnum::EXPLORATION_FUEL)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setStartCharge(0)
            ->setMaxCharge(24)
            ->setAutoRemove(false)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($explorationFuelStatus);

        $hunterTruceCycles = new ChargeStatusConfig();
        $hunterTruceCycles
            ->setStatusName(HunterStatusEnum::TRUCE_CYCLES)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setStartCharge(2)
            ->setMaxCharge(2)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_DECREMENT)
            ->setAutoRemove(true)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($hunterTruceCycles);

        $followingHuntersStatus = new ChargeStatusConfig();
        $followingHuntersStatus
            ->setStatusName(DaedalusStatusEnum::FOLLOWING_HUNTERS)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setStartCharge(0)
            ->setMaxCharge(null)
            ->setAutoRemove(false)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($followingHuntersStatus);

        $changedCpuPriority = new ChargeStatusConfig();
        $changedCpuPriority
            ->setStatusName('changed_cpu_priority')
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
            ->setStartCharge(1)
            ->setAutoRemove(true)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($changedCpuPriority);

        $autoWateringFiresKilled = new ChargeStatusConfig();
        $autoWateringFiresKilled
            ->setStatusName(DaedalusStatusEnum::AUTO_WATERING_KILLED_FIRES)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::NONE)
            ->setStartCharge(1)
            ->setAutoRemove(false)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($autoWateringFiresKilled);

        $droneCharges = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(\sprintf('%s_%s_default', EquipmentStatusEnum::ELECTRIC_CHARGES, ItemEnum::SUPPORT_DRONE))
        );
        $manager->persist($droneCharges);

        $hasChitchattedStatus = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::HAS_CHITCHATTED . '_default')
        );
        $manager->persist($hasChitchattedStatus);

        $modifierPlayerAlwaysSucceedsRepairAction = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_ALWAYS_SUCCEEDS_REPAIR_ACTION)
        );
        $this->addReference($modifierPlayerAlwaysSucceedsRepairAction->getName(), $modifierPlayerAlwaysSucceedsRepairAction);
        $manager->persist($modifierPlayerAlwaysSucceedsRepairAction);

        $geniusIdea = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::GENIUS_IDEA . '_default')
        );
        $geniusIdea->setModifierConfigs([$modifierPlayerAlwaysSucceedsRepairAction]);
        $manager->persist($geniusIdea);

        $ceasefireModifier = EventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::CEASEFIRE)
        );
        $manager->persist($ceasefireModifier);
        $ceasefireStatus = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(PlaceStatusEnum::CEASEFIRE->value . '_default')
        );
        $ceasefireStatus->setModifierConfigs([$ceasefireModifier]);
        $manager->persist($ceasefireStatus);

        $hasIssuedMissionStatus = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::HAS_ISSUED_MISSION . '_default')
        );
        $manager->persist($hasIssuedMissionStatus);

        $deloggedStatus = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(PlaceStatusEnum::DELOGGED->toString() . '_default')
        );
        $manager->persist($deloggedStatus);

        $hasUsedDelogStatus = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::HAS_USED_DELOG . '_default')
        );
        $manager->persist($hasUsedDelogStatus);

        $slimeTrapStatus = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::SLIME_TRAP . '_default')
        );
        $manager->persist($slimeTrapStatus);

        $slimedStatus = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(EquipmentStatusEnum::SLIMED . '_default')
        );
        $manager->persist($slimedStatus);

        $turboDroneUpgrade = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(EquipmentStatusEnum::TURBO_DRONE_UPGRADE . '_default')
        );
        $manager->persist($turboDroneUpgrade);

        $droneRepairFailedAttempts = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(EquipmentStatusEnum::DRONE_REPAIR_FAILED_ATTEMPTS . '_default')
        );
        $manager->persist($droneRepairFailedAttempts);

        $droneExtinguishFailedAttempts = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(EquipmentStatusEnum::DRONE_EXTINGUISH_FAILED_ATTEMPTS . '_default')
        );
        $manager->persist($droneExtinguishFailedAttempts);

        $droneShootHunterFailedAttempts = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(EquipmentStatusEnum::DRONE_SHOOT_HUNTER_FAILED_ATTEMPTS . '_default')
        );
        $manager->persist($droneShootHunterFailedAttempts);

        /** @var VariableEventModifierConfig $lyingDownModifier */
        $lyingDownModifier = $this->getReference(StatusModifierConfigFixtures::LYING_DOWN_MODIFIER);

        /** @var ActionConfig $getUpAction */
        $getUpAction = $this->getReference(ActionEnum::GET_UP->value);

        $lyingDown = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::LYING_DOWN . '_default')
        );
        $lyingDown
            ->setModifierConfigs([$lyingDownModifier])
            ->setActionConfigs([$getUpAction]);

        $manager->persist($lyingDown);

        $antiquePerfumeImmunized = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::ANTIQUE_PERFUME_IMMUNIZED . '_default')
        );
        $manager->persist($antiquePerfumeImmunized);

        $hasDaunted = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::HAS_DAUNTED . '_default')
        );
        $manager->persist($hasDaunted);

        $hasGenMetal = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::HAS_GEN_METAL . '_default')
        );
        $manager->persist($hasGenMetal);

        $hasSabotagedDoor = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::HAS_SABOTAGED_DOOR . '_default')
        );
        $manager->persist($hasSabotagedDoor);

        $neronDepression = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(DaedalusStatusEnum::NERON_DEPRESSION . '_default')
        );
        $manager->persist($neronDepression);

        $coffeeThermosCharge = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName('electric_charges_coffee_thermos_default')
        );
        $manager->persist($coffeeThermosCharge);

        $lunchboxCharge = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName('electric_charges_lunchbox_default')
        );
        $manager->persist($lunchboxCharge);

        $contactedSolToday = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::CONTACTED_SOL_TODAY . '_default')
        );
        $manager->persist($contactedSolToday);

        $baseRebelContactDuration = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(DaedalusStatusEnum::REBEL_BASE_CONTACT_DURATION . '_default')
        );
        $baseRebelContactDuration->setStartCharge(8);
        $manager->persist($baseRebelContactDuration);

        $accidentCooldown = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::SELECTED_FOR_STEEL_PLATE . '_default')
        );
        $manager->persist($accidentCooldown);

        $boardDiseaseCooldown = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::SELECTED_FOR_BOARD_DISEASE . '_default')
        );
        $manager->persist($boardDiseaseCooldown);

        $electrocutionCooldown = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(PlaceStatusEnum::SELECTED_FOR_ELECTROCUTION->toString() . '_default')
        );
        $manager->persist($electrocutionCooldown);

        $joltCooldown = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(PlaceStatusEnum::SELECTED_FOR_JOLT->toString() . '_default')
        );
        $manager->persist($joltCooldown);

        $anxietyAttackCooldown = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::SELECTED_FOR_ANXIETY_ATTACK . '_default')
        );
        $manager->persist($anxietyAttackCooldown);

        $personalTriumphRegression = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::PERSONAL_TRIUMPH_REGRESSION . '_default')
        );
        $manager->persist($personalTriumphRegression);

        $gameConfig
            ->addStatusConfig($noGravityRepaired)
            ->addStatusConfig($attemptConfig)
            ->addStatusConfig($scooterCharge)
            ->addStatusConfig($oldFaithfulCharge)
            ->addStatusConfig($bigWeaponCharge)
            ->addStatusConfig($turretCharge)
            ->addStatusConfig($microwaveCharge)
            ->addStatusConfig($coffeeCharge)
            ->addStatusConfig($dispenserCharge)
            ->addStatusConfig($blasterCharge)
            ->addStatusConfig($patrolShipCharge)
            ->addStatusConfig($sinkCharge)
            ->addStatusConfig($fireStatus)
            ->addStatusConfig($plantYoung)
            ->addStatusConfig($eurekaMoment)
            ->addStatusConfig($firstTime)
            ->addStatusConfig($mushStatus)
            ->addStatusConfig($contaminated)
            ->addStatusConfig($drug_eaten)
            ->addStatusConfig($did_the_thing)
            ->addStatusConfig($did_boring_speech)
            ->addStatusConfig($updatingTrackie)
            ->addStatusConfig($already_washed_in_the_sink)
            ->addStatusConfig($asteroidTruceCycles)
            ->addStatusConfig($rejuvenationCharge)
            ->addStatusConfig($patrolShipArmor)
            ->addStatusConfig($pasiphaeArmor)
            ->addStatusConfig($traveling)
            ->addStatusConfig($explorationOxygenStatus)
            ->addStatusConfig($explorationFuelStatus)
            ->addStatusConfig($hunterTruceCycles)
            ->addStatusConfig($followingHuntersStatus)
            ->addStatusConfig($changedCpuPriority)
            ->addStatusConfig($autoWateringFiresKilled)
            ->addStatusConfig($droneCharges)
            ->addStatusConfig($ceasefireStatus)
            ->addStatusConfig($hasChitchattedStatus)
            ->addStatusConfig($geniusIdea)
            ->addStatusConfig($hasIssuedMissionStatus)
            ->addStatusConfig($slimedStatus)
            ->addStatusConfig($deloggedStatus)
            ->addStatusConfig($hasUsedDelogStatus)
            ->addStatusConfig($slimeTrapStatus)
            ->addStatusConfig($turboDroneUpgrade)
            ->addStatusConfig($droneRepairFailedAttempts)
            ->addStatusConfig($droneExtinguishFailedAttempts)
            ->addStatusConfig($droneShootHunterFailedAttempts)
            ->addStatusConfig($neronDepression)
            ->addStatusConfig($hasSabotagedDoor)
            ->addStatusConfig($hasGenMetal)
            ->addStatusConfig($lyingDown)
            ->addStatusConfig($antiquePerfumeImmunized)
            ->addStatusConfig($hasDaunted)
            ->addStatusConfig($coffeeThermosCharge)
            ->addStatusConfig($contactedSolToday)
            ->addStatusConfig($baseRebelContactDuration)
            ->addStatusConfig($lunchboxCharge)
            ->addStatusConfig($accidentCooldown)
            ->addStatusConfig($boardDiseaseCooldown)
            ->addStatusConfig($electrocutionCooldown)
            ->addStatusConfig($joltCooldown)
            ->addStatusConfig($anxietyAttackCooldown)
            ->addStatusConfig($personalTriumphRegression);

        $manager->persist($gameConfig);

        $manager->flush();

        $this->addReference(self::SCOOTER_CHARGE, $scooterCharge);
        $this->addReference(self::OLDFAITHFUL_CHARGE, $oldFaithfulCharge);
        $this->addReference(self::BIG_WEAPON_CHARGE, $bigWeaponCharge);
        $this->addReference(self::TURRET_CHARGE, $turretCharge);
        $this->addReference(self::MICROWAVE_CHARGE, $microwaveCharge);
        $this->addReference(self::COFFEE_CHARGE, $coffeeCharge);
        $this->addReference(self::DISPENSER_CHARGE, $dispenserCharge);
        $this->addReference(self::BLASTER_CHARGE, $blasterCharge);
        $this->addReference(self::PATROLLER_CHARGE, $patrolShipCharge);
        $this->addReference(self::SINK_CHARGE, $sinkCharge);

        $this->addReference(self::FIRE_STATUS, $fireStatus);
        $this->addReference(self::PLANT_YOUNG, $plantYoung);
        $this->addReference(self::EUREKA_MOMENT, $eurekaMoment);
        $this->addReference(self::FIRST_TIME, $firstTime);
        $this->addReference(self::MUSH_STATUS, $mushStatus);
        $this->addReference(self::CONTAMINATED_FOOD, $contaminated);
        $this->addReference(self::DRUG_EATEN_STATUS, $drug_eaten);
        $this->addReference(self::DID_THE_THING_STATUS, $did_the_thing);
        $this->addReference(self::DID_BORING_SPEECH_STATUS, $did_boring_speech);
        $this->addReference(self::UPDATING_TRACKIE_STATUS, $updatingTrackie);
        $this->addReference(self::ALREADY_WASHED_IN_THE_SINK, $already_washed_in_the_sink);
        $this->addReference(self::ASTEROID_TRUCE_CYCLES, $asteroidTruceCycles);
        $this->addReference(self::PATROL_SHIP_ARMOR, $patrolShipArmor);
        $this->addReference(self::PASIPHAE_ARMOR, $pasiphaeArmor);
        $this->addReference(self::HAS_REJUVENATED, $rejuvenationCharge);
        $this->addReference(self::TRAVELING_STATUS, $traveling);
        $this->addReference(self::EXPLORATION_OXYGEN_STATUS, $explorationOxygenStatus);
        $this->addReference(self::EXPLORATION_FUEL_STATUS, $explorationFuelStatus);
        $this->addReference(self::HUNTER_TRUCE_CYCLES, $hunterTruceCycles);
        $this->addReference(self::FOLLOWING_HUNTERS_STATUS, $followingHuntersStatus);
        $this->addReference(self::CHANGED_CPU_PRIORITY, $changedCpuPriority);
        $this->addReference(DaedalusStatusEnum::AUTO_WATERING_KILLED_FIRES, $autoWateringFiresKilled);
        $this->addReference(EquipmentStatusEnum::ELECTRIC_CHARGES . '_' . ItemEnum::SUPPORT_DRONE, $droneCharges);
        $this->addReference(PlayerStatusEnum::HAS_CHITCHATTED, $hasChitchattedStatus);
        $this->addReference($geniusIdea->getName(), $geniusIdea);
        $this->addReference($hasChitchattedStatus->getName(), $hasChitchattedStatus);
        $this->addReference($ceasefireStatus->getName(), $ceasefireStatus);
        $this->addReference($hasIssuedMissionStatus->getName(), $hasIssuedMissionStatus);
        $this->addReference($deloggedStatus->getName(), $deloggedStatus);
        $this->addReference($hasUsedDelogStatus->getName(), $hasUsedDelogStatus);
        $this->addReference($slimeTrapStatus->getName(), $slimeTrapStatus);
        $this->addReference(EquipmentStatusEnum::SLIMED, $slimedStatus);
        $this->addReference(EquipmentStatusEnum::TURBO_DRONE_UPGRADE, $turboDroneUpgrade);
        $this->addReference(EquipmentStatusEnum::DRONE_REPAIR_FAILED_ATTEMPTS, $droneRepairFailedAttempts);
        $this->addReference(EquipmentStatusEnum::DRONE_EXTINGUISH_FAILED_ATTEMPTS, $droneExtinguishFailedAttempts);
        $this->addReference(EquipmentStatusEnum::DRONE_SHOOT_HUNTER_FAILED_ATTEMPTS, $droneShootHunterFailedAttempts);
        $this->addReference(PlayerStatusEnum::LYING_DOWN . '_default', $lyingDown);
        $this->addReference(PlayerStatusEnum::ANTIQUE_PERFUME_IMMUNIZED, $antiquePerfumeImmunized);
        $this->addReference(PlayerStatusEnum::HAS_DAUNTED, $hasDaunted);
        $this->addReference(PlayerStatusEnum::HAS_GEN_METAL, $hasGenMetal);
        $this->addReference(PlayerStatusEnum::HAS_SABOTAGED_DOOR, $hasSabotagedDoor);
        $this->addReference(DaedalusStatusEnum::NERON_DEPRESSION, $neronDepression);
        $this->addReference(self::COFFEE_THERMOS_CHARGE, $coffeeThermosCharge);
        $this->addReference(DaedalusStatusEnum::REBEL_BASE_CONTACT_DURATION, $baseRebelContactDuration);
        $this->addReference(self::LUNCHBOX_CHARGE, $lunchboxCharge);
        $this->addReference(PlayerStatusEnum::SELECTED_FOR_STEEL_PLATE, $accidentCooldown);
        $this->addReference(PlayerStatusEnum::SELECTED_FOR_BOARD_DISEASE, $boardDiseaseCooldown);
        $this->addReference(PlaceStatusEnum::SELECTED_FOR_ELECTROCUTION->toString(), $electrocutionCooldown);
        $this->addReference(PlaceStatusEnum::SELECTED_FOR_JOLT->toString(), $joltCooldown);
        $this->addReference(PlayerStatusEnum::SELECTED_FOR_ANXIETY_ATTACK, $anxietyAttackCooldown);
        $this->addReference(PlayerStatusEnum::PERSONAL_TRIUMPH_REGRESSION, $personalTriumphRegression);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            StatusModifierConfigFixtures::class,
            GearModifierConfigFixtures::class,
            ActionsFixtures::class,
            SkillPointsFixtures::class,
        ];
    }
}
