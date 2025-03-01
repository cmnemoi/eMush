<?php

namespace Mush\Action\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\ConfigData\ActionData;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Enum\PlayerVariableEnum;

class ActionsFixtures extends Fixture
{
    public const string SUICIDE = 'suicide';
    public const string AUTO_DESTROY = 'auto_destruction';
    public const string KILL_PLAYER = 'kill_player';
    public const string RESET_SKILL_POINT = ActionEnum::RESET_SKILL_POINTS->value;
    public const string REJUVENATE_ALPHA = 'rejuvenate_alpha';
    public const string UPDATING_TALKIE = 'updating_talkie';
    public const string MOVE_DEFAULT = 'move_default';
    public const string SEARCH_DEFAULT = 'search_default';
    public const string HIT_DEFAULT = 'hit_default';
    public const string HIDE_DEFAULT = 'hide_default';
    public const string DEFAULT_TAKE = 'default_take';
    public const string DEFAULT_DROP = 'default_drop';
    public const string DO_THE_THING = 'do_the_thing';
    public const string DRUG_CONSUME = 'drug_consume';
    public const string RATION_CONSUME = 'ration_consume';
    public const string BUILD_DEFAULT = 'build_default';
    public const string READ_DOCUMENT = 'read_document';
    public const string READ_BOOK = 'read_book';
    public const string ATTACK_DEFAULT = 'attack_default';
    public const string EXTINGUISH_DEFAULT = 'extinguish_default';
    public const string TRY_KUBE = 'try_kube';
    public const string OPEN_SPACE_CAPSULE = 'open_space_capsule';
    public const string INJECT_SERUM = 'inject_serum';
    public const string BANDAGE_DEFAULT = 'bandage_default';
    public const string COOK_EXPRESS = 'cook_express';
    public const string COOK_DEFAULT = 'cook_default';
    public const string HEAL = 'heal';
    public const string SELF_HEAL = 'self.heal';
    public const string HEAL_ULTRA = 'heal.ultra';
    public const string COMFORT = 'comfort';
    public const string WRITE = 'write';
    public const string SHRED = 'shred';
    public const string GAG_DEFAULT = 'gag_default';
    public const string UNGAG_DEFAULT = 'ungag_default';
    public const string HYPERFREEZE_DEFAULT = 'hyperfreeze_default';
    public const string SHOWER_DEFAULT = 'shower_default';
    public const string WASH_IN_SINK = 'wash_in_sink';
    public const string FLIRT_DEFAULT = 'flirt_default';
    public const string FUEL_INJECT = 'fuel_inject';
    public const string FUEL_RETRIEVE = 'fuel_retrieve';
    public const string OXYGEN_INJECT = 'oxygen_inject';
    public const string STRENGTHEN_HULL = 'strength_hull';
    public const string OXYGEN_RETRIEVE = 'oxygen_retrieve';
    public const string LIE_DOWN = 'lie_down';
    public const string GET_UP = 'get_up';
    public const string COFFEE_DEFAULT = 'coffee_default';
    public const string DISPENSE_DRUG = 'dispense_drug';
    public const string TRANSPLANT = 'transplant';
    public const string TREAT_PLANT = 'treat_plant';
    public const string WATER_PLANT = 'water_plant';
    public const string REPORT_EQUIPMENT = 'report_equipment';
    public const string REPORT_FIRE = 'report_fire';
    public const string INSTALL_CAMERA = 'install_camera';
    public const string REMOVE_CAMERA = 'remove_camera';
    public const string CHECK_SPORE_LEVEL = 'check_spore_level';
    public const string EXAMINE_EQUIPMENT = 'examine_equipment';
    public const string REMOVE_SPORE = 'remove_spore';
    public const string PUBLIC_BROADCAST = 'public_broadcast';
    public const string EXTINGUISH_MANUALLY = 'extinguish_manually';
    public const string MOTIVATIONAL_SPEECH = 'motivational_speech';
    public const string BORING_SPEECH = 'boring_speech';
    public const string SURGERY = 'surgery';
    public const string SELF_SURGERY = 'self_surgery';
    public const string SHOOT = 'shoot';
    public const string SHOOT_99 = 'shoot_99_percent';
    public const string PLAY_ARCADE = 'play_arcade';
    public const string SHOOT_HUNTER_TURRET = 'shoot_hunter_turret';
    public const string SHOOT_RANDOM_HUNTER_TURRET = 'shoot_random_hunter_turret';
    public const string TAKEOFF = 'takeoff';
    public const string ACCESS_TERMINAL = 'access_terminal';
    public const string LAND = 'land';
    public const string SHOOT_HUNTER_PATROL_SHIP = 'shoot_hunter_patrol_ship';
    public const string SHOOT_RANDOM_HUNTER_PATROL_SHIP = 'shoot_random_hunter_patrol_ship';
    public const string COLLECT_SCRAP = 'collect_scrap';
    public const string RENOVATE = 'renovate';
    public const string CONVERT_ACTION_TO_MOVEMENT = 'convert_action_to_movement';
    public const string AUTO_EJECT = 'auto_eject';
    public const string INSERT_FUEL_CHAMBER = 'insert_fuel_chamber';
    public const string RETRIEVE_FUEL_CHAMBER = 'retrieve_fuel_chamber';
    public const string CHECK_FUEL_CHAMBER_LEVEL = 'check_fuel_chamber_level';
    public const string HACK = 'hack';
    public const string EXIT_TERMINAL = 'exit_terminal';
    public const string ADVANCE_DAEDALUS = 'advance_daedalus';
    public const string SCAN = 'scan';
    public const string ANALYZE_PLANET = 'analyze_planet';
    public const string TURN_DAEDALUS_LEFT = 'turn_daedalus_left';
    public const string TURN_DAEDALUS_RIGHT = 'turn_daedalus_right';
    public const string DELETE_PLANET = 'delete_planet';
    public const string LEAVE_ORBIT = 'leave_orbit';
    public const string TAKEOFF_TO_PLANET = 'takeoff_to_planet';
    public const string TAKEOFF_TO_PLANET_PATROL_SHIP = 'takeoff_to_planet_patrol_ship';
    public const string CHANGE_NERON_CPU_PRIORITY = 'change_neron_cpu_priority';
    public const string TAKE_CAT = 'take_cat';
    public const string PET_CAT = 'pet_cat';
    public const string SHOOT_CAT = 'shoot_cat';
    public const string OPEN_CONTAINER_COST_0 = 'open_container_cost_0';

    public function load(ObjectManager $manager): void
    {
        /** @TODO remove this after alpha */
        $suicide = new ActionConfig();
        $suicide
            ->setName(ActionEnum::SUICIDE->value)
            ->setActionName(ActionEnum::SUICIDE)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::PLAYER);
        $manager->persist($suicide);

        $autoDestroy = new ActionConfig();
        $autoDestroy
            ->setName(ActionEnum::AUTO_DESTROY->value)
            ->setActionName(ActionEnum::AUTO_DESTROY)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);
        $manager->persist($autoDestroy);

        $killPlayer = new ActionConfig();
        $killPlayer
            ->setName(ActionEnum::KILL_PLAYER->value)
            ->setActionName(ActionEnum::KILL_PLAYER)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER);
        $manager->persist($killPlayer);

        $rejuvenateAlpha = new ActionConfig();
        $rejuvenateAlpha
            ->setActionName(ActionEnum::REJUVENATE)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->buildName(GameConfigEnum::ALPHA);
        $manager->persist($rejuvenateAlpha);

        $resetSpecializationPoint = new ActionConfig();
        $resetSpecializationPoint
            ->setActionName(ActionEnum::RESET_SKILL_POINTS)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setRange(ActionRangeEnum::PLAYER)
            ->buildName(GameConfigEnum::ALPHA);
        $manager->persist($resetSpecializationPoint);

        $updatingTalkie = new ActionConfig();
        $updatingTalkie
            ->setName(ActionEnum::UPDATE_TALKIE->value)
            ->setActionName(ActionEnum::UPDATE_TALKIE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setInjuryRate(10)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);

        $manager->persist($updatingTalkie);

        $moveAction = new ActionConfig();
        $moveAction
            ->setName(ActionEnum::MOVE->value)
            ->setActionName(ActionEnum::MOVE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setMovementCost(1);
        $manager->persist($moveAction);

        $searchAction = new ActionConfig();
        $searchAction
            ->setName(ActionEnum::SEARCH->value)
            ->setActionName(ActionEnum::SEARCH)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setActionCost(1);
        $manager->persist($searchAction);

        $hitAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::HIT));
        $manager->persist($hitAction);

        $hideAction = new ActionConfig();
        $hideAction
            ->setName(ActionEnum::HIDE->value)
            ->setActionName(ActionEnum::HIDE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);
        $manager->persist($hideAction);

        $takeItemAction = new ActionConfig();
        $takeItemAction
            ->setName(ActionEnum::TAKE->value)
            ->setActionName(ActionEnum::TAKE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setInjuryRate(1);

        $manager->persist($takeItemAction);

        $dropItemAction = new ActionConfig();
        $dropItemAction
            ->setName(ActionEnum::DROP->value)
            ->setActionName(ActionEnum::DROP)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);

        $manager->persist($dropItemAction);

        $rationConsumeAction = new ActionConfig();
        $rationConsumeAction
            ->setName(ActionEnum::CONSUME->value)
            ->setActionName(ActionEnum::CONSUME)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setDirtyRate(15)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);

        $manager->persist($rationConsumeAction);

        $drugConsumeAction = new ActionConfig();
        $drugConsumeAction
            ->setName(ActionEnum::CONSUME_DRUG->value)
            ->setActionName(ActionEnum::CONSUME_DRUG)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setDirtyRate(15)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET)
            ->setTypes([ActionEnum::CONSUME]);

        $manager->persist($drugConsumeAction);

        $buildAction = new ActionConfig();
        $buildAction
            ->setName(ActionEnum::BUILD->value)
            ->setActionName(ActionEnum::BUILD)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(3)
            ->setDirtyRate(25)
            ->setInjuryRate(5);

        $manager->persist($buildAction);

        $readAction = new ActionConfig();
        $readAction
            ->setName(ActionEnum::READ_BOOK->value)
            ->setActionName(ActionEnum::READ_BOOK)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2);

        $manager->persist($readAction);

        $readDocument = new ActionConfig();
        $readDocument
            ->setName(ActionEnum::READ_DOCUMENT->value)
            ->setActionName(ActionEnum::READ_DOCUMENT)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);

        $manager->persist($readDocument);

        $attackAction = new ActionConfig();
        $attackAction
            ->setName(ActionEnum::ATTACK->value)
            ->setActionName(ActionEnum::ATTACK)
            ->setTypes([ActionTypeEnum::ACTION_AGGRESSIVE, ActionTypeEnum::ACTION_ATTACK])
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->setSuccessRate(60)
            ->setDirtyRate(15)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::CRITICAL_SUCCESS, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::CRITICAL_FAIL, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::ONE_SHOT, VisibilityEnum::PUBLIC);

        $manager->persist($attackAction);

        $extinguishAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::EXTINGUISH));
        $manager->persist($extinguishAction);

        $tryKubeAction = new ActionConfig();
        $tryKubeAction
            ->setName(ActionEnum::TRY_KUBE->value)
            ->setActionName(ActionEnum::TRY_KUBE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1);

        $manager->persist($tryKubeAction);

        $openSpaceCapsuleAction = new ActionConfig();
        $openSpaceCapsuleAction
            ->setName(ActionEnum::OPEN->value)
            ->setActionName(ActionEnum::OPEN)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setInjuryRate(1);

        $manager->persist($openSpaceCapsuleAction);

        $injectSerumAction = new ActionConfig();
        $injectSerumAction
            ->setName(ActionEnum::CURE->value)
            ->setActionName(ActionEnum::CURE)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(1);

        $manager->persist($injectSerumAction);

        $bandageAction = new ActionConfig();
        $bandageAction
            ->setName(ActionEnum::USE_BANDAGE->value)
            ->setActionName(ActionEnum::USE_BANDAGE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setDirtyRate(5)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
            ->setOutputQuantity(2);

        $manager->persist($bandageAction);

        $expressCookAction = new ActionConfig();
        $expressCookAction
            ->setName(ActionEnum::EXPRESS_COOK->value)
            ->setActionName(ActionEnum::EXPRESS_COOK)
            ->setRange(ActionRangeEnum::SHELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setDirtyRate(20)
            ->setOutputQuantity(30);

        $manager->persist($expressCookAction);

        $cookAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::COOK));
        $manager->persist($cookAction);

        $selfHealAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SELF_HEAL));
        $manager->persist($selfHealAction);

        $healAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::HEAL));
        $manager->persist($healAction);

        $comfortAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::COMFORT));
        $manager->persist($comfortAction);

        $ultraHealAction = new ActionConfig();
        $ultraHealAction
            ->setName(ActionEnum::ULTRAHEAL->value)
            ->setActionName(ActionEnum::ULTRAHEAL)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);

        $manager->persist($ultraHealAction);

        $writeAction = new ActionConfig();
        $writeAction
            ->setName(ActionEnum::WRITE->value)
            ->setActionName(ActionEnum::WRITE)
            ->setRange(ActionRangeEnum::SHELF)
            ->setDisplayHolder(ActionHolderEnum::TERMINAL);

        $manager->persist($writeAction);

        $shredAction = new ActionConfig();
        $shredAction
            ->setName(ActionEnum::SHRED->value)
            ->setActionName(ActionEnum::SHRED)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);

        $manager->persist($shredAction);

        $hyperfreezeAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::HYPERFREEZE));
        $manager->persist($hyperfreezeAction);

        $gagAction = new ActionConfig();
        $gagAction
            ->setName(ActionEnum::GAG->value)
            ->setActionName(ActionEnum::GAG)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(1);

        $manager->persist($gagAction);

        $ungagAction = new ActionConfig();
        $ungagAction
            ->setName(ActionEnum::UNGAG->value)
            ->setActionName(ActionEnum::UNGAG)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setActionCost(1);

        $manager->persist($ungagAction);

        $showerAction = new ActionConfig();
        $showerAction
            ->setName(ActionEnum::TAKE_SHOWER->value)
            ->setActionName(ActionEnum::TAKE_SHOWER)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->setInjuryRate(2)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);

        $manager->persist($showerAction);

        $sinkAction = new ActionConfig();
        $sinkAction
            ->setName(ActionEnum::WASH_IN_SINK->value)
            ->setActionName(ActionEnum::WASH_IN_SINK)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(3)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);

        $manager->persist($sinkAction);

        $fuelInjectAction = new ActionConfig();
        $fuelInjectAction
            ->setName(ActionEnum::INSERT_FUEL->value)
            ->setActionName(ActionEnum::INSERT_FUEL)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setDirtyRate(15)
            ->setInjuryRate(1)
            ->setOutputQuantity(1); // amount of fuel inserted

        $manager->persist($fuelInjectAction);

        $retrieveFuelAction = new ActionConfig();
        $retrieveFuelAction
            ->setName(ActionEnum::RETRIEVE_FUEL->value)
            ->setActionName(ActionEnum::RETRIEVE_FUEL)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setDirtyRate(15)
            ->setInjuryRate(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);

        $manager->persist($retrieveFuelAction);

        $oxygenInjectAction = new ActionConfig();
        $oxygenInjectAction
            ->setName(ActionEnum::INSERT_OXYGEN->value)
            ->setActionName(ActionEnum::INSERT_OXYGEN)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setInjuryRate(1)
            ->setOutputQuantity(1) // amount of fuel inserted
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);

        $manager->persist($oxygenInjectAction);

        $retrieveOxygenAction = new ActionConfig();
        $retrieveOxygenAction
            ->setName(ActionEnum::RETRIEVE_OXYGEN->value)
            ->setActionName(ActionEnum::RETRIEVE_OXYGEN)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setInjuryRate(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);

        $manager->persist($retrieveOxygenAction);

        $strengthenHullAction = new ActionConfig();
        $strengthenHullAction
            ->setName(ActionEnum::STRENGTHEN_HULL->value)
            ->setActionName(ActionEnum::STRENGTHEN_HULL)
            ->setTypes([ActionTypeEnum::ACTION_TECHNICIAN])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setDirtyRate(50)
            ->setInjuryRate(5)
            ->setSuccessRate(25)
            ->setOutputQuantity(5);

        $manager->persist($strengthenHullAction);

        $lieDownActon = new ActionConfig();
        $lieDownActon
            ->setName(ActionEnum::LIE_DOWN->value)
            ->setActionName(ActionEnum::LIE_DOWN)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);

        $manager->persist($lieDownActon);

        $getUpAction = new ActionConfig();
        $getUpAction
            ->setName(ActionEnum::GET_UP->value)
            ->setActionName(ActionEnum::GET_UP)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::PLAYER);

        $manager->persist($getUpAction);

        $coffeeAction = new ActionConfig();
        $coffeeAction
            ->setName(ActionEnum::COFFEE->value)
            ->setActionName(ActionEnum::COFFEE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setDirtyRate(3);

        $manager->persist($coffeeAction);

        $dispenseAction = new ActionConfig();
        $dispenseAction
            ->setName(ActionEnum::DISPENSE->value)
            ->setActionName(ActionEnum::DISPENSE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);

        $manager->persist($dispenseAction);

        $transplantAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TRANSPLANT));
        $manager->persist($transplantAction);

        $treatPlantAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TREAT_PLANT));
        $manager->persist($treatPlantAction);

        $waterPlantAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::WATER_PLANT));
        $manager->persist($waterPlantAction);

        $reportEquipmentAction = new ActionConfig();
        $reportEquipmentAction
            ->setName(ActionEnum::REPORT_EQUIPMENT->value)
            ->setActionName(ActionEnum::REPORT_EQUIPMENT)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);

        $manager->persist($reportEquipmentAction);

        $reportFireAction = new ActionConfig();
        $reportFireAction
            ->setName(ActionEnum::REPORT_FIRE->value)
            ->setActionName(ActionEnum::REPORT_FIRE)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::PLAYER);

        $manager->persist($reportFireAction);

        $installCameraAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::INSTALL_CAMERA));
        $manager->persist($installCameraAction);

        $removeCameraAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::REMOVE_CAMERA));
        $manager->persist($removeCameraAction);

        $examineEquipmentAction = new ActionConfig();
        $examineEquipmentAction
            ->setName(ActionEnum::EXAMINE->value)
            ->setActionName(ActionEnum::EXAMINE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);

        $manager->persist($examineEquipmentAction);

        $checkSporeLevelAction = new ActionConfig();
        $checkSporeLevelAction
            ->setName(ActionEnum::CHECK_SPORE_LEVEL->value)
            ->setActionName(ActionEnum::CHECK_SPORE_LEVEL)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);

        $manager->persist($checkSporeLevelAction);

        $flirtAction = new ActionConfig();
        $flirtAction
            ->setName(ActionEnum::FLIRT->value)
            ->setActionName(ActionEnum::FLIRT)
            ->setTypes([ActionTypeEnum::ACTION_CONFIRM])
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(1);

        $manager->persist($flirtAction);

        $doTheThingAction = new ActionConfig();
        $doTheThingAction
            ->setName(ActionEnum::DO_THE_THING->value)
            ->setActionName(ActionEnum::DO_THE_THING)
            ->setTypes([ActionTypeEnum::ACTION_CONFIRM])
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->setOutputQuantity(2)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::PUBLIC);

        $manager->persist($doTheThingAction);

        $removeSporeAction = new ActionConfig();
        $removeSporeAction
            ->setName(ActionEnum::REMOVE_SPORE->value)
            ->setActionName(ActionEnum::REMOVE_SPORE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);

        $manager->persist($removeSporeAction);

        $publicBroadcastAction = new ActionConfig();
        $publicBroadcastAction
            ->setName(ActionEnum::PUBLIC_BROADCAST->value)
            ->setActionName(ActionEnum::PUBLIC_BROADCAST)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->setOutputQuantity(3);

        $manager->persist($publicBroadcastAction);

        $extinguishManuallyAction = new ActionConfig();
        $extinguishManuallyAction
            ->setName(ActionEnum::EXTINGUISH_MANUALLY->value)
            ->setActionName(ActionEnum::EXTINGUISH_MANUALLY)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setActionCost(1)
            ->setDirtyRate(50)
            ->setInjuryRate(5)
            ->setSuccessRate(10);

        $manager->persist($extinguishManuallyAction);

        $motivationalSpeechAction = new ActionConfig();
        $motivationalSpeechAction
            ->setName(ActionEnum::MOTIVATIONAL_SPEECH->value)
            ->setActionName(ActionEnum::MOTIVATIONAL_SPEECH)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setTypes([ActionTypeEnum::ACTION_SPOKEN])
            ->setActionCost(2)
            ->setOutputQuantity(2);

        $manager->persist($motivationalSpeechAction);

        $boringSpeechAction = new ActionConfig();
        $boringSpeechAction
            ->setName(ActionEnum::BORING_SPEECH->value)
            ->setActionName(ActionEnum::BORING_SPEECH)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setTypes([ActionTypeEnum::ACTION_SPOKEN])
            ->setActionCost(2)
            ->setOutputQuantity(3);

        $manager->persist($boringSpeechAction);

        $surgeryAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SURGERY));
        $manager->persist($surgeryAction);

        $selfSurgeryAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SELF_SURGERY));
        $manager->persist($selfSurgeryAction);

        $shootAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SHOOT));
        $manager->persist($shootAction);

        $shoot99Action = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SHOOT_99));
        $manager->persist($shoot99Action);

        $playArcade = new ActionConfig();
        $playArcade
            ->setName(ActionEnum::PLAY_ARCADE->value)
            ->setActionName(ActionEnum::PLAY_ARCADE)
            ->setTypes([ActionTypeEnum::ACTION_CONFIRM])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setSuccessRate(33)
            ->setOutputQuantity(2);
        $manager->persist($playArcade);

        $shootHunterTurret = new ActionConfig();
        $shootHunterTurret
            ->setName(ActionEnum::SHOOT_HUNTER->value . '_turret')
            ->setActionName(ActionEnum::SHOOT_HUNTER)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::HUNTER)
            ->setTypes([ActionTypeEnum::ACTION_SHOOT_HUNTER])
            ->setActionCost(1)
            ->setSuccessRate(30)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::HIDDEN);
        $manager->persist($shootHunterTurret);

        $shootRandomHunterTurret = new ActionConfig();
        $shootRandomHunterTurret
            ->setName(ActionEnum::SHOOT_RANDOM_HUNTER->value . '_turret')
            ->setActionName(ActionEnum::SHOOT_RANDOM_HUNTER)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setTypes([ActionTypeEnum::ACTION_SHOOT_HUNTER])
            ->setActionCost(1)
            ->setSuccessRate(30)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::HIDDEN);
        $manager->persist($shootRandomHunterTurret);

        $takeoff = new ActionConfig();
        $takeoff
            ->setName(ActionEnum::TAKEOFF->value)
            ->setActionName(ActionEnum::TAKEOFF)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setTypes([ActionTypeEnum::ACTION_PILOT])
            ->setActionCost(2)
            ->setSuccessRate(100)
            ->setCriticalRate(20)
            ->setVisibility(ActionOutputEnum::CRITICAL_SUCCESS, VisibilityEnum::HIDDEN)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::HIDDEN);
        $manager->persist($takeoff);

        $accessTerminal = new ActionConfig();
        $accessTerminal
            ->setName(ActionEnum::ACCESS_TERMINAL->value)
            ->setActionName(ActionEnum::ACCESS_TERMINAL)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);
        $manager->persist($accessTerminal);

        $land = new ActionConfig();
        $land
            ->setName(ActionEnum::LAND->value)
            ->setActionName(ActionEnum::LAND)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setTypes([ActionTypeEnum::ACTION_PILOT])
            ->setActionCost(2)
            ->setSuccessRate(100)
            ->setCriticalRate(20)
            ->setVisibility(ActionOutputEnum::CRITICAL_SUCCESS, VisibilityEnum::HIDDEN)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::HIDDEN);
        $manager->persist($land);

        $shootHunterPatrolShip = new ActionConfig();
        $shootHunterPatrolShip
            ->setName(ActionEnum::SHOOT_HUNTER->value . '_patrolship')
            ->setActionName(ActionEnum::SHOOT_HUNTER_PATROL_SHIP)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::HUNTER)
            ->setTypes([ActionTypeEnum::ACTION_SHOOT_HUNTER])
            ->setActionCost(1)
            ->setSuccessRate(40)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::HIDDEN);
        $manager->persist($shootHunterPatrolShip);

        $shootRandomHunterPatrolShip = new ActionConfig();
        $shootRandomHunterPatrolShip
            ->setName(ActionEnum::SHOOT_RANDOM_HUNTER_PATROL_SHIP->value)
            ->setActionName(ActionEnum::SHOOT_RANDOM_HUNTER_PATROL_SHIP)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setTypes([ActionTypeEnum::ACTION_SHOOT_HUNTER])
            ->setActionCost(1)
            ->setSuccessRate(40)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::HIDDEN);
        $manager->persist($shootRandomHunterPatrolShip);

        $collectScrap = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::COLLECT_SCRAP));
        $manager->persist($collectScrap);

        $renovate = new ActionConfig();
        $renovate
            ->setName(ActionEnum::RENOVATE->value)
            ->setActionName(ActionEnum::RENOVATE)
            ->setTypes([ActionTypeEnum::ACTION_TECHNICIAN])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->setSuccessRate(12)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::PRIVATE);
        $manager->persist($renovate);

        $convertActionToMovement = new ActionConfig();
        $convertActionToMovement
            ->setName(ActionEnum::CONVERT_ACTION_TO_MOVEMENT->value)
            ->setActionName(ActionEnum::CONVERT_ACTION_TO_MOVEMENT)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::PLAYER);
        $convertActionToMovement->getGameVariables()->setValuesByName(['value' => 1, 'min_value' => 0, 'max_value' => null], PlayerVariableEnum::ACTION_POINT);
        $convertActionToMovement->getGameVariables()->setValuesByName(['value' => -3, 'min_value' => null, 'max_value' => 0], PlayerVariableEnum::MOVEMENT_POINT);
        $manager->persist($convertActionToMovement);

        $autoEject = new ActionConfig();
        $autoEject
            ->setName(ActionEnum::AUTO_EJECT->value)
            ->setActionName(ActionEnum::AUTO_EJECT)
            ->setTypes([ActionTypeEnum::ACTION_CONFIRM])
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setActionCost(1)
            ->setInjuryRate(25)
            ->setDirtyRate(25);
        $manager->persist($autoEject);

        $insertFuelChamber = new ActionConfig();
        $insertFuelChamber
            ->setName(ActionEnum::INSERT_FUEL_CHAMBER->value)
            ->setActionName(ActionEnum::INSERT_FUEL_CHAMBER)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setDirtyRate(15)
            ->setInjuryRate(1)
            ->setOutputQuantity(1); // amount of oxygen inserted
        $manager->persist($insertFuelChamber);

        $retrieveFuelChamber = new ActionConfig();
        $retrieveFuelChamber
            ->setName(ActionEnum::RETRIEVE_FUEL_CHAMBER->value)
            ->setActionName(ActionEnum::RETRIEVE_FUEL_CHAMBER)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setDirtyRate(15)
            ->setInjuryRate(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);
        $manager->persist($retrieveFuelChamber);

        $checkFuelChamberLevel = new ActionConfig();
        $checkFuelChamberLevel
            ->setName(ActionEnum::CHECK_FUEL_CHAMBER_LEVEL->value)
            ->setActionName(ActionEnum::CHECK_FUEL_CHAMBER_LEVEL)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setDirtyRate(5)
            ->setInjuryRate(0)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);
        $manager->persist($checkFuelChamberLevel);

        $hack = new ActionConfig();
        $hack
            ->setName(ActionEnum::HACK->value)
            ->setActionName(ActionEnum::HACK)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);
        $manager->persist($hack);

        $exitTerminal = new ActionConfig();
        $exitTerminal
            ->setName(ActionEnum::EXIT_TERMINAL->value)
            ->setActionName(ActionEnum::EXIT_TERMINAL)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::TERMINAL)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::HIDDEN)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN);
        $manager->persist($exitTerminal);

        $advanceDaedalus = new ActionConfig();
        $advanceDaedalus
            ->setName(ActionEnum::ADVANCE_DAEDALUS->value)
            ->setActionName(ActionEnum::ADVANCE_DAEDALUS)
            ->setTypes([ActionTypeEnum::ACTION_CONFIRM])
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::TERMINAL)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::NO_FUEL, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::ARACK_PREVENTS_TRAVEL, VisibilityEnum::PUBLIC);
        $manager->persist($advanceDaedalus);

        $scan = new ActionConfig();
        $scan
            ->setName(ActionEnum::SCAN->value)
            ->setActionName(ActionEnum::SCAN)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::TERMINAL)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
            ->setActionCost(3)
            ->setSuccessRate(50);
        $manager->persist($scan);

        $analyzePlanet = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::ANALYZE_PLANET));
        $manager->persist($analyzePlanet);

        $turnDaedalusLeft = new ActionConfig();
        $turnDaedalusLeft
            ->setName(ActionEnum::TURN_DAEDALUS_LEFT->value)
            ->setActionName(ActionEnum::TURN_DAEDALUS_LEFT)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::TERMINAL)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::HIDDEN)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN);
        $manager->persist($turnDaedalusLeft);

        $turnDaedalusRight = new ActionConfig();
        $turnDaedalusRight
            ->setName(ActionEnum::TURN_DAEDALUS_RIGHT->value)
            ->setActionName(ActionEnum::TURN_DAEDALUS_RIGHT)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::TERMINAL)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::HIDDEN)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN);
        $manager->persist($turnDaedalusRight);

        $deletePlanet = new ActionConfig();
        $deletePlanet
            ->setName(ActionEnum::DELETE_PLANET->value)
            ->setActionName(ActionEnum::DELETE_PLANET)
            ->setTypes([ActionTypeEnum::ACTION_CONFIRM])
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::PLANET)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN);
        $manager->persist($deletePlanet);

        $leaveOrbit = new ActionConfig();
        $leaveOrbit
            ->setName(ActionEnum::LEAVE_ORBIT->value)
            ->setActionName(ActionEnum::LEAVE_ORBIT)
            ->setTypes([ActionTypeEnum::ACTION_CONFIRM])
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::TERMINAL)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::NO_FUEL, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::ARACK_PREVENTS_TRAVEL, VisibilityEnum::PUBLIC);
        $manager->persist($leaveOrbit);

        $takeoffToPlanet = new ActionConfig();
        $takeoffToPlanet
            ->setName(ActionEnum::TAKEOFF_TO_PLANET->value)
            ->setActionName(ActionEnum::TAKEOFF_TO_PLANET)
            ->setTypes([ActionTypeEnum::ACTION_CONFIRM])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN)
            ->setOutputQuantity(4); // max number of explorators allowed
        $manager->persist($takeoffToPlanet);

        $takeoffToPlanetPatrolShip = new ActionConfig();
        $takeoffToPlanetPatrolShip
            ->setName(ActionEnum::TAKEOFF_TO_PLANET_PATROL_SHIP->value)
            ->setActionName(ActionEnum::TAKEOFF_TO_PLANET_PATROL_SHIP)
            ->setTypes([ActionTypeEnum::ACTION_CONFIRM])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN)
            ->setOutputQuantity(1); // max number of explorators allowed
        $manager->persist($takeoffToPlanetPatrolShip);

        $changeNeronCpuPriority = new ActionConfig();
        $changeNeronCpuPriority
            ->setName(ActionEnum::CHANGE_NERON_CPU_PRIORITY->value)
            ->setActionName(ActionEnum::CHANGE_NERON_CPU_PRIORITY)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::TERMINAL)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN);
        $manager->persist($changeNeronCpuPriority);

        $repairPilgred = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::REPAIR_PILGRED));
        $manager->persist($repairPilgred);

        $returnToSol = new ActionConfig();
        $returnToSol
            ->setName(ActionEnum::RETURN_TO_SOL->value)
            ->setActionName(ActionEnum::RETURN_TO_SOL)
            ->setTypes([ActionTypeEnum::ACTION_CONFIRM])
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::TERMINAL)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::HIDDEN)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN);
        $manager->persist($returnToSol);

        $participate = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::PARTICIPATE));
        $manager->persist($participate);

        $changeNeronCrewLock = new ActionConfig();
        $changeNeronCrewLock
            ->setName(ActionEnum::CHANGE_NERON_CREW_LOCK->value)
            ->setActionName(ActionEnum::CHANGE_NERON_CREW_LOCK)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::TERMINAL)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN);
        $manager->persist($changeNeronCrewLock);

        $togglePlasmaShield = new ActionConfig();
        $togglePlasmaShield
            ->setName(ActionEnum::TOGGLE_PLASMA_SHIELD->value)
            ->setActionName(ActionEnum::TOGGLE_PLASMA_SHIELD)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::TERMINAL)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN);
        $manager->persist($togglePlasmaShield);

        $toggleMagneticNet = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TOGGLE_MAGNETIC_NET));
        $manager->persist($toggleMagneticNet);

        $chitchat = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CHITCHAT));
        $manager->persist($chitchat);

        $graft = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::GRAFT));
        $manager->persist($graft);

        $learn = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::LEARN));
        $manager->persist($learn);

        $putThroughDoor = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::PUT_THROUGH_DOOR));
        $manager->persist($putThroughDoor);

        $becomeGenius = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::BECOME_GENIUS));
        $manager->persist($becomeGenius);

        $premonition = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::PREMONITION));
        $manager->persist($premonition);

        $ceasefire = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CEASEFIRE));
        $manager->persist($ceasefire);

        $guard = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::GUARD));
        $manager->persist($guard);

        $commanderOrder = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::COMMANDER_ORDER));
        $manager->persist($commanderOrder);

        $acceptMission = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::ACCEPT_MISSION));
        $manager->persist($acceptMission);

        $rejectMission = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::REJECT_MISSION));
        $manager->persist($rejectMission);

        $printZeList = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::PRINT_ZE_LIST));
        $manager->persist($printZeList);

        $throwGrenade = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::THROW_GRENADE));
        $manager->persist($throwGrenade);

        $toggleNeronInhibition = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TOGGLE_NERON_INHIBITION));
        $manager->persist($toggleNeronInhibition);

        $delog = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::DELOG));
        $manager->persist($delog);

        $runHome = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::RUN_HOME));
        $manager->persist($runHome);

        $putsch = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::PUTSCH));
        $manager->persist($putsch);

        $anathema = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::ANATHEMA));
        $manager->persist($anathema);

        $mixRationSpore = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::MIX_RATION_SPORE));
        $manager->persist($mixRationSpore);

        $depress = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::DEPRESS));
        $manager->persist($depress);

        $slimeTrap = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SLIME_TRAP));
        $manager->persist($slimeTrap);

        $slimeObject = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SLIME_OBJECT));
        $manager->persist($slimeObject);

        $massGgedon = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::MASS_GGEDON));
        $manager->persist($massGgedon);

        $reinforce = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::REINFORCE));
        $manager->persist($reinforce);

        $upgradeDroneToTurbo = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::UPGRADE_DRONE_TO_TURBO));
        $manager->persist($upgradeDroneToTurbo);

        $upgradeDroneToFirefighter = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::UPGRADE_DRONE_TO_FIREFIGHTER));
        $manager->persist($upgradeDroneToFirefighter);

        $upgradeDroneToPilot = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::UPGRADE_DRONE_TO_PILOT));
        $manager->persist($upgradeDroneToPilot);

        $takeCat = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TAKE_CAT));
        $manager->persist($takeCat);

        $petCat = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::PET_CAT));
        $manager->persist($petCat);

        $shootCat = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SHOOT_CAT));
        $manager->persist($shootCat);

        $torture = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TORTURE));
        $manager->persist($torture);

        $daunt = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::DAUNT));
        $manager->persist($daunt);

        $genMetal = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::GEN_METAL));
        $manager->persist($genMetal);

        $doorSabotage = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::DOOR_SABOTAGE));
        $manager->persist($doorSabotage);

        $giveNightmare = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::GIVE_NIGHTMARE));
        $manager->persist($giveNightmare);

        $neronDepress = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::NERON_DEPRESS));
        $manager->persist($neronDepress);

        $searchForMushGenome = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SEARCH_FOR_MUSH_GENOME));
        $manager->persist($searchForMushGenome);

        $participateResearch = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::PARTICIPATE_RESEARCH));
        $manager->persist($participateResearch);

        $computeEden = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::COMPUTE_EDEN));
        $manager->persist($computeEden);

        $travelToEden = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TRAVEL_TO_EDEN));
        $manager->persist($travelToEden);

        $comManagerAnnouncement = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::COM_MANAGER_ANNOUNCEMENT));
        $manager->persist($comManagerAnnouncement);

        $establishLinkWithSol = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::ESTABLISH_LINK_WITH_SOL));
        $manager->persist($establishLinkWithSol);

        $upgradeNeron = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::UPGRADE_NERON));
        $manager->persist($upgradeNeron);

        $decodeRebelSignal = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::DECODE_REBEL_SIGNAL));
        $manager->persist($decodeRebelSignal);

        $contactXyloph = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CONTACT_XYLOPH));
        $manager->persist($contactXyloph);

        $openContainerCost0 = new ActionConfig();
        $openContainerCost0
            ->setName('open_container_cost_0')
            ->setActionName(ActionEnum::OPEN_CONTAINER)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN)
            ->setActionCost(0)
            ->setDirtyRate(0)
            ->setInjuryRate(0)
            ->setSuccessRate(100);
        $manager->persist($openContainerCost0);

        $manager->flush();

        $this->addReference(self::SUICIDE, $suicide);
        $this->addReference(self::AUTO_DESTROY, $autoDestroy);
        $this->addReference(self::KILL_PLAYER, $killPlayer);

        $this->addReference(self::REJUVENATE_ALPHA, $rejuvenateAlpha);
        $this->addReference(self::RESET_SKILL_POINT, $resetSpecializationPoint);
        $this->addReference(self::UPDATING_TALKIE, $updatingTalkie);

        $this->addReference(self::MOVE_DEFAULT, $moveAction);
        $this->addReference(self::SEARCH_DEFAULT, $searchAction);
        $this->addReference(self::HIT_DEFAULT, $hitAction);
        $this->addReference(self::HIDE_DEFAULT, $hideAction);
        $this->addReference(self::DEFAULT_TAKE, $takeItemAction);
        $this->addReference(self::DEFAULT_DROP, $dropItemAction);
        $this->addReference(self::RATION_CONSUME, $rationConsumeAction);
        $this->addReference(self::DRUG_CONSUME, $drugConsumeAction);
        $this->addReference(self::BUILD_DEFAULT, $buildAction);
        $this->addReference(self::READ_DOCUMENT, $readDocument);
        $this->addReference(self::READ_BOOK, $readAction);
        $this->addReference(self::ATTACK_DEFAULT, $attackAction);
        $this->addReference(ActionEnum::EXTINGUISH->value, $extinguishAction);
        $this->addReference(self::TRY_KUBE, $tryKubeAction);
        $this->addReference(self::OPEN_SPACE_CAPSULE, $openSpaceCapsuleAction);
        $this->addReference(self::INJECT_SERUM, $injectSerumAction);
        $this->addReference(self::BANDAGE_DEFAULT, $bandageAction);
        $this->addReference(self::COOK_EXPRESS, $expressCookAction);
        $this->addReference(self::COOK_DEFAULT, $cookAction);
        $this->addReference(self::HEAL, $healAction);
        $this->addReference(self::SELF_HEAL, $selfHealAction);
        $this->addReference(self::HEAL_ULTRA, $ultraHealAction);
        $this->addReference($comfortAction->getName(), $comfortAction);
        $this->addReference(self::WRITE, $writeAction);
        $this->addReference(self::SHRED, $shredAction);
        $this->addReference(self::HYPERFREEZE_DEFAULT, $hyperfreezeAction);
        $this->addReference(self::GAG_DEFAULT, $gagAction);
        $this->addReference(self::UNGAG_DEFAULT, $ungagAction);
        $this->addReference(self::SHOWER_DEFAULT, $showerAction);
        $this->addReference(self::WASH_IN_SINK, $sinkAction);
        $this->addReference(self::FUEL_INJECT, $fuelInjectAction);
        $this->addReference(self::FUEL_RETRIEVE, $retrieveFuelAction);
        $this->addReference(self::OXYGEN_INJECT, $oxygenInjectAction);
        $this->addReference(self::OXYGEN_RETRIEVE, $retrieveOxygenAction);
        $this->addReference(self::STRENGTHEN_HULL, $strengthenHullAction);
        $this->addReference(self::LIE_DOWN, $lieDownActon);
        $this->addReference(self::GET_UP, $getUpAction);
        $this->addReference(self::COFFEE_DEFAULT, $coffeeAction);
        $this->addReference(self::DISPENSE_DRUG, $dispenseAction);
        $this->addReference(self::TRANSPLANT, $transplantAction);
        $this->addReference(self::TREAT_PLANT, $treatPlantAction);
        $this->addReference(self::WATER_PLANT, $waterPlantAction);
        $this->addReference(self::REPORT_FIRE, $reportFireAction);
        $this->addReference(self::REPORT_EQUIPMENT, $reportEquipmentAction);
        $this->addReference(self::INSTALL_CAMERA, $installCameraAction);
        $this->addReference(self::REMOVE_CAMERA, $removeCameraAction);
        $this->addReference(self::EXAMINE_EQUIPMENT, $examineEquipmentAction);
        $this->addReference(self::CHECK_SPORE_LEVEL, $checkSporeLevelAction);
        $this->addReference(self::FLIRT_DEFAULT, $flirtAction);
        $this->addReference(self::DO_THE_THING, $doTheThingAction);
        $this->addReference(self::REMOVE_SPORE, $removeSporeAction);
        $this->addReference(self::PUBLIC_BROADCAST, $publicBroadcastAction);
        $this->addReference(self::EXTINGUISH_MANUALLY, $extinguishManuallyAction);
        $this->addReference(self::MOTIVATIONAL_SPEECH, $motivationalSpeechAction);
        $this->addReference(self::BORING_SPEECH, $boringSpeechAction);
        $this->addReference(self::SURGERY, $surgeryAction);
        $this->addReference(self::SELF_SURGERY, $selfSurgeryAction);
        $this->addReference(self::SHOOT, $shootAction);
        $this->addReference(self::SHOOT_99, $shoot99Action);
        $this->addReference(self::PLAY_ARCADE, $playArcade);
        $this->addReference(self::SHOOT_HUNTER_TURRET, $shootHunterTurret);
        $this->addReference(self::SHOOT_RANDOM_HUNTER_TURRET, $shootRandomHunterTurret);
        $this->addReference(self::TAKEOFF, $takeoff);
        $this->addReference(self::ACCESS_TERMINAL, $accessTerminal);
        $this->addReference(self::LAND, $land);
        $this->addReference(self::SHOOT_HUNTER_PATROL_SHIP, $shootHunterPatrolShip);
        $this->addReference(self::SHOOT_RANDOM_HUNTER_PATROL_SHIP, $shootRandomHunterPatrolShip);
        $this->addReference(self::COLLECT_SCRAP, $collectScrap);
        $this->addReference(self::RENOVATE, $renovate);
        $this->addReference(self::CONVERT_ACTION_TO_MOVEMENT, $convertActionToMovement);
        $this->addReference(self::AUTO_EJECT, $autoEject);
        $this->addReference(self::INSERT_FUEL_CHAMBER, $insertFuelChamber);
        $this->addReference(self::RETRIEVE_FUEL_CHAMBER, $retrieveFuelChamber);
        $this->addReference(self::CHECK_FUEL_CHAMBER_LEVEL, $checkFuelChamberLevel);
        $this->addReference(self::HACK, $hack);
        $this->addReference(self::EXIT_TERMINAL, $exitTerminal);
        $this->addReference(self::ADVANCE_DAEDALUS, $advanceDaedalus);
        $this->addReference(self::SCAN, $scan);
        $this->addReference(self::ANALYZE_PLANET, $analyzePlanet);
        $this->addReference(self::TURN_DAEDALUS_LEFT, $turnDaedalusLeft);
        $this->addReference(self::TURN_DAEDALUS_RIGHT, $turnDaedalusRight);
        $this->addReference(self::DELETE_PLANET, $deletePlanet);
        $this->addReference(self::LEAVE_ORBIT, $leaveOrbit);
        $this->addReference(self::TAKEOFF_TO_PLANET, $takeoffToPlanet);
        $this->addReference(self::TAKEOFF_TO_PLANET_PATROL_SHIP, $takeoffToPlanetPatrolShip);
        $this->addReference(self::CHANGE_NERON_CPU_PRIORITY, $changeNeronCpuPriority);
        $this->addReference(ActionEnum::REPAIR_PILGRED->value, $repairPilgred);
        $this->addReference(ActionEnum::RETURN_TO_SOL->value, $returnToSol);
        $this->addReference(ActionEnum::PARTICIPATE->value, $participate);
        $this->addReference(ActionEnum::CHANGE_NERON_CREW_LOCK->value, $changeNeronCrewLock);
        $this->addReference(ActionEnum::TOGGLE_PLASMA_SHIELD->value, $togglePlasmaShield);
        $this->addReference(ActionEnum::TOGGLE_MAGNETIC_NET->value, $toggleMagneticNet);
        $this->addReference(ActionEnum::CHITCHAT->value, $chitchat);
        $this->addReference(ActionEnum::GRAFT->value, $graft);
        $this->addReference(ActionEnum::LEARN->value, $learn);
        $this->addReference(ActionEnum::PUT_THROUGH_DOOR->value, $putThroughDoor);
        $this->addReference(ActionEnum::BECOME_GENIUS->value, $becomeGenius);
        $this->addReference(ActionEnum::PREMONITION->value, $premonition);
        $this->addReference(ActionEnum::CEASEFIRE->value, $ceasefire);
        $this->addReference(ActionEnum::GUARD->value, $guard);
        $this->addReference(ActionEnum::COMMANDER_ORDER->value, $commanderOrder);
        $this->addReference(ActionEnum::ACCEPT_MISSION->value, $acceptMission);
        $this->addReference(ActionEnum::REJECT_MISSION->value, $rejectMission);
        $this->addReference(ActionEnum::PRINT_ZE_LIST->value, $printZeList);
        $this->addReference(ActionEnum::THROW_GRENADE->value, $throwGrenade);
        $this->addReference(ActionEnum::TOGGLE_NERON_INHIBITION->value, $toggleNeronInhibition);
        $this->addReference(ActionEnum::DELOG->value, $delog);
        $this->addReference(ActionEnum::RUN_HOME->value, $runHome);
        $this->addReference(ActionEnum::PUTSCH->value, $putsch);
        $this->addReference(ActionEnum::ANATHEMA->value, $anathema);
        $this->addReference(ActionEnum::MIX_RATION_SPORE->value, $mixRationSpore);
        $this->addReference(ActionEnum::DEPRESS->value, $depress);
        $this->addReference(ActionEnum::SLIME_TRAP->value, $slimeTrap);
        $this->addReference(ActionEnum::SLIME_OBJECT->value, $slimeObject);
        $this->addReference(ActionEnum::MASS_GGEDON->value, $massGgedon);
        $this->addReference(ActionEnum::REINFORCE->value, $reinforce);
        $this->addReference(ActionEnum::UPGRADE_DRONE_TO_TURBO->value, $upgradeDroneToTurbo);
        $this->addReference(ActionEnum::UPGRADE_DRONE_TO_FIREFIGHTER->value, $upgradeDroneToFirefighter);
        $this->addReference(ActionEnum::UPGRADE_DRONE_TO_PILOT->value, $upgradeDroneToPilot);
        $this->addReference(ActionEnum::TAKE_CAT->value, $takeCat);
        $this->addReference(ActionEnum::PET_CAT->value, $petCat);
        $this->addReference(ActionEnum::SHOOT_CAT->value, $shootCat);
        $this->addReference(ActionEnum::TORTURE->value, $torture);
        $this->addReference(ActionEnum::DAUNT->value, $daunt);
        $this->addReference(ActionEnum::GEN_METAL->value, $genMetal);
        $this->addReference(ActionEnum::DOOR_SABOTAGE->value, $doorSabotage);
        $this->addReference(ActionEnum::GIVE_NIGHTMARE->value, $giveNightmare);
        $this->addReference(ActionEnum::NERON_DEPRESS->value, $neronDepress);
        $this->addReference(ActionEnum::SEARCH_FOR_MUSH_GENOME->value, $searchForMushGenome);
        $this->addReference(ActionEnum::PARTICIPATE_RESEARCH->value, $participateResearch);
        $this->addReference(ActionEnum::COMPUTE_EDEN->value, $computeEden);
        $this->addReference(ActionEnum::TRAVEL_TO_EDEN->value, $travelToEden);
        $this->addReference(self::OPEN_CONTAINER_COST_0, $openContainerCost0);
        $this->addReference(ActionEnum::COM_MANAGER_ANNOUNCEMENT->value, $comManagerAnnouncement);
        $this->addReference(ActionEnum::ESTABLISH_LINK_WITH_SOL->value, $establishLinkWithSol);
        $this->addReference(ActionEnum::UPGRADE_NERON->value, $upgradeNeron);
        $this->addReference(ActionEnum::DECODE_REBEL_SIGNAL->value, $decodeRebelSignal);
        $this->addReference(ActionEnum::CONTACT_XYLOPH->value, $contactXyloph);
    }
}
