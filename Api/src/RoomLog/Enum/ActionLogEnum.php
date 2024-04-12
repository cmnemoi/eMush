<?php

namespace Mush\RoomLog\Enum;

use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\ActionOutputEnum;

abstract class ActionLogEnum
{
    public const string SUICIDE_SUCCESS = 'suicide_success';
    public const string DISASSEMBLE_SUCCESS = 'disassemble_success';
    public const string DISASSEMBLE_FAIL = 'disassemble_fail';
    public const string BUILD_SUCCESS = 'build_success';
    public const string COFFEE_SUCCESS = 'coffee_success';
    public const string COMFORT_SUCCESS = 'comfort_success';
    public const string CONSUME_SUCCESS = 'consume_success';
    public const string CONSUME_DRUG = 'consume_drug';
    public const string COOK_SUCCESS = 'cook_success';
    public const string DISPENSE_SUCCESS = 'dispense_success';
    public const string DO_THE_THING_SUCCESS = 'do_the_thing_success';
    public const string DO_THE_THING_BREAKS_SOFA = 'do_the_thing_breaks_sofa';
    public const string DROP = 'drop';
    public const string EXPRESS_COOK_SUCCESS = 'express_cook_success';
    public const string EXTINGUISH_SUCCESS = 'extinguish_success';
    public const string EXTINGUISH_FAIL = 'extinguish_fail';
    public const string EXTRACT_SPORE_SUCCESS = 'extract_spore_success';
    public const string FLIRT_SUCCESS = 'flirt_success';
    public const string GAG_SUCCESS = 'gag_success';
    public const string GET_UP = 'get_up';
    public const string HEAL_SUCCESS = 'heal_success';
    public const string HIDE_SUCCESS = 'hide_success';
    public const string HIT_CRITICAL_SUCCESS = 'hit_critical_success';
    public const string HIT_SUCCESS = 'hit_success';
    public const string HIT_FAIL = 'hit_fail';
    public const string HYBRIDIZE_SUCCESS = 'hybridize_success';
    public const string HYBRIDIZE_FAIL = 'transplant_fail';
    public const string HYPERFREEZE_SUCCESS = 'hyperfreeze_success';
    public const string PHAGOCYTE_SUCCESS = 'phagocyte_success';
    public const string INFECT_SUCCESS = 'infect_success';
    public const string INSERT_FUEL = 'insert_fuel';
    public const string INSERT_OXYGEN = 'insert_oxygen';
    public const string RETRIEVE_OXYGEN = 'retrieve_oxygen';
    public const string RETRIEVE_FUEL = 'retrieve_fuel';
    public const string LIE_DOWN = 'lie_down';
    public const string EXIT_ROOM = 'exit_room';
    public const string ENTER_ROOM = 'enter_room';
    public const string READ_BOOK = 'read_book';
    public const string READ_DOCUMENT = 'read_document';
    public const string READ_CONTENT = 'read_content';
    public const string REPAIR_SUCCESS = 'repair_success';
    public const string REPAIR_FAIL = 'repair_fail';
    public const string SABOTAGE_SUCCESS = 'sabotage_success';
    public const string SABOTAGE_FAIL = 'sabotage_fail';
    public const string SEARCH_SUCCESS = 'search_success';
    public const string SEARCH_FAIL = 'search_fail';
    public const string SHRED_SUCCESS = 'shred_success';
    public const string SHOWER_HUMAN = 'shower_human';
    public const string SHOWER_MUSH = 'shower_mush';
    public const string WASH_IN_SINK_HUMAN = 'wash_in_sink_human';
    public const string WASH_IN_SINK_MUSH = 'wash_in_sink_mush';
    public const string STRENGTHEN_SUCCESS = 'strengthen_success';
    public const string SPREAD_FIRE_SUCCESS = 'spread_fire_success';
    public const string TAKE = 'take';
    public const string TRANSPLANT_SUCCESS = 'transplant_success';
    public const string TREAT_PLANT_SUCCESS = 'treat_plant_success';
    public const string TRY_KUBE = 'try_kube';
    public const string ULTRAHEAL_SUCCESS = 'ultraheal_success';
    public const string UNGAG_SUCCESS = 'ungag_success';
    public const string SELF_HEAL = 'self_heal';
    public const string WATER_PLANT_SUCCESS = 'water_plant_success';
    public const string OPEN_SUCCESS = 'open_success';
    public const string INSTALL_CAMERA = 'install_camera';
    public const string REMOVE_CAMERA = 'remove_camera';
    public const string CHECK_SPORE_LEVEL = 'check_spore_level';
    public const string REMOVE_SPORE_SUCCESS = 'remove_spore_success';
    public const string REMOVE_SPORE_FAIL = 'remove_spore_fail';
    public const string PUBLIC_BROADCAST = 'public_broadcast';
    public const string MOTIVATIONAL_SPEECH = 'motivational_speech';
    public const string BORING_SPEECH = 'boring_speech';
    public const string MAKE_SICK = 'make_sick';
    public const string FAKE_DISEASE = 'fake_disease';
    public const string FAIL_SURGERY = 'fail_surgery';
    public const string FAIL_SELF_SURGERY = 'fail_self_surgery';
    public const string UPDATE_TALKIE_SUCCESS = 'update_talkie_success';
    public const string SCREW_TALKIE_SUCCESS = 'screw_talkie_success';
    public const string ATTACK_SUCCESS = 'attack_success';
    public const string ATTACK_FAIL = 'attack_fail';
    public const string ATTACK_CRITICAL_SUCCESS = 'attack_critical_success';
    public const string ATTACK_CRITICAL_FAIL = 'attack_critical_fail';
    public const string ATTACK_ONE_SHOT = 'attack_one_shot';
    public const string SHOOT_SUCCESS = 'shoot_success';
    public const string SHOOT_FAIL = 'shoot_fail';
    public const string SHOOT_CRITICAL_SUCCESS = 'shoot_critical_success';
    public const string SHOOT_CRITICAL_FAIL = 'shoot_critical_fail';
    public const string SHOOT_ONE_SHOT = 'shoot_one_shot';
    public const string SHOOT_HUNTER_SUCCESS = 'shoot_hunter_success';
    public const string SHOOT_HUNTER_FAIL = 'shoot_hunter_fail';
    public const string TAKEOFF_SUCCESS = 'takeoff_success';
    public const string ACCESS_TERMINAL_SUCCESS = 'access_terminal_success';
    public const string TAKEOFF_NO_PILOT = 'takeoff_no_pilot';
    public const string LAND_NO_PILOT = 'land_no_pilot';
    public const string LAND_SUCCESS = 'land_success';
    public const string SHOOT_HUNTER_PATROL_SHIP_SUCCESS = 'shoot_hunter_patrol_ship_success';
    public const string SHOOT_HUNTER_PATROL_SHIP_FAIL = 'shoot_hunter_patrol_ship_fail';
    public const string RENOVATE_SUCCESS = 'renovate_success';
    public const string RENOVATE_FAIL = 'renovate_fail';
    public const string AUTO_EJECT_SUCCESS = 'auto_eject_success';
    public const string INSERT_FUEL_CHAMBER_SUCCESS = 'insert_fuel_chamber_success';
    public const string RETRIEVE_FUEL_CHAMBER_SUCCESS = 'retrieve_fuel_chamber_success';
    public const string CHECK_FUEL_CHAMBER_LEVEL_SUCCESS = 'check_fuel_chamber_level_success';
    public const string HACK_SUCCESS = 'hack_success';
    public const string HACK_FAIL = 'hack_fail';
    public const string ADVANCE_DAEDALUS_SUCCESS = 'advance_daedalus_success';
    public const string ADVANCE_DAEDALUS_FAIL = 'advance_daedalus_fail';
    public const string ADVANCE_DAEDALUS_NO_FUEL = 'advance_daedalus_no_fuel';
    public const string ADVANCE_DAEDALUS_ARACK_PREVENTS_TRAVEL = 'advance_daedalus_arack_prevents_travel';
    public const string SCAN_SUCCESS = 'scan_success';
    public const string SCAN_FAIL = 'scan_fail';
    public const string ANALYZE_PLANET_SUCCESS = 'analyze_planet_success';
    public const string DELETE_PLANET_SUCCESS = 'delete_planet_success';
    public const string TAKEOFF_TO_PLANET_SUCCESS = 'takeoff_to_planet_success';
    public const string TAKEOFF_TO_PLANET_PATROL_SHIP_SUCCESS = 'takeoff_to_planet_patrol_ship_success';
    public const string CHANGE_NERON_CPU_PRIORITY_SUCCESS = 'change_neron_cpu_priority_success';
    public const string DEFAULT_FAIL = 'default_fail';
    public const string VISIBILITY = 'visibility';
    public const string VALUE = 'value';
    public const string PLAY_ARCADE_SUCCESS = 'play_arcade_success';
    public const string PLAY_ARCADE_FAIL = 'play_arcade_fail';

    public const array ACTION_LOGS = [
        ActionEnum::DISASSEMBLE => [
            ActionOutputEnum::SUCCESS => self::DISASSEMBLE_SUCCESS,
            ActionOutputEnum::FAIL => self::DISASSEMBLE_FAIL,
        ],
        ActionEnum::TAKE => [
            ActionOutputEnum::SUCCESS => self::TAKE,
        ],
        ActionEnum::HIDE => [
            ActionOutputEnum::SUCCESS => self::HIDE_SUCCESS,
        ],
        ActionEnum::DROP => [
            ActionOutputEnum::SUCCESS => self::DROP,
        ],
        ActionEnum::REPAIR => [
            ActionOutputEnum::SUCCESS => self::REPAIR_SUCCESS,
            ActionOutputEnum::FAIL => self::REPAIR_FAIL,
        ],
        ActionEnum::SEARCH => [
            ActionOutputEnum::SUCCESS => self::SEARCH_SUCCESS,
            ActionOutputEnum::FAIL => self::SEARCH_FAIL,
        ],
        ActionEnum::EXTRACT_SPORE => [
            ActionOutputEnum::SUCCESS => self::EXTRACT_SPORE_SUCCESS,
        ],
        ActionEnum::INFECT => [
            ActionOutputEnum::SUCCESS => self::INFECT_SUCCESS,
        ],
        ActionEnum::SABOTAGE => [
            ActionOutputEnum::SUCCESS => self::SABOTAGE_SUCCESS,
            ActionOutputEnum::FAIL => self::SABOTAGE_FAIL,
        ],
        ActionEnum::READ_DOCUMENT => [
            ActionOutputEnum::SUCCESS => self::READ_DOCUMENT,
        ],
        ActionEnum::READ_BOOK => [
            ActionOutputEnum::SUCCESS => self::READ_BOOK,
        ],
        ActionEnum::SHRED => [
            ActionOutputEnum::SUCCESS => self::SHRED_SUCCESS,
        ],
        ActionEnum::CONSUME => [
            ActionOutputEnum::SUCCESS => self::CONSUME_SUCCESS,
        ],
        ActionEnum::CONSUME_DRUG => [
            ActionOutputEnum::SUCCESS => self::CONSUME_DRUG,
        ],
        ActionEnum::PHAGOCYTE => [
            ActionOutputEnum::SUCCESS => self::PHAGOCYTE_SUCCESS,
        ],
        ActionEnum::WATER_PLANT => [
            ActionOutputEnum::SUCCESS => self::WATER_PLANT_SUCCESS,
        ],
        ActionEnum::TREAT_PLANT => [
            ActionOutputEnum::SUCCESS => self::TREAT_PLANT_SUCCESS,
        ],
        ActionEnum::TRY_KUBE => [
            ActionOutputEnum::SUCCESS => self::TRY_KUBE,
        ],
        ActionEnum::HYBRIDIZE => [
            ActionOutputEnum::SUCCESS => self::HYBRIDIZE_SUCCESS,
            ActionOutputEnum::FAIL => self::HYBRIDIZE_FAIL,
        ],
        ActionEnum::EXTINGUISH => [
            ActionOutputEnum::SUCCESS => self::EXTINGUISH_SUCCESS,
            ActionOutputEnum::FAIL => self::EXTINGUISH_FAIL,
        ],
        ActionEnum::HYPERFREEZE => [
            ActionOutputEnum::SUCCESS => self::HYPERFREEZE_SUCCESS,
        ],
        ActionEnum::EXPRESS_COOK => [
            ActionOutputEnum::SUCCESS => self::COOK_SUCCESS,
        ],
        ActionEnum::INSERT_OXYGEN => [
            ActionOutputEnum::SUCCESS => self::INSERT_OXYGEN,
        ],
        ActionEnum::RETRIEVE_OXYGEN => [
            ActionOutputEnum::SUCCESS => self::RETRIEVE_OXYGEN,
        ],
        ActionEnum::INSERT_FUEL => [
            ActionOutputEnum::SUCCESS => self::INSERT_FUEL,
        ],
        ActionEnum::RETRIEVE_FUEL => [
            ActionOutputEnum::SUCCESS => self::RETRIEVE_FUEL,
        ],
        ActionEnum::COOK => [
            ActionOutputEnum::SUCCESS => self::COOK_SUCCESS,
        ],
        ActionEnum::COFFEE => [
            ActionOutputEnum::SUCCESS => self::COFFEE_SUCCESS,
        ],
        ActionEnum::DISPENSE => [
            ActionOutputEnum::SUCCESS => self::DISPENSE_SUCCESS,
        ],
        ActionEnum::SHOWER => [
            ActionOutputEnum::SUCCESS => self::SHOWER_HUMAN,
            ActionOutputEnum::FAIL => self::SHOWER_MUSH,
        ],
        ActionEnum::WASH_IN_SINK => [
            ActionOutputEnum::SUCCESS => self::WASH_IN_SINK_HUMAN,
            ActionOutputEnum::FAIL => self::WASH_IN_SINK_MUSH,
        ],
        ActionEnum::LIE_DOWN => [
            ActionOutputEnum::SUCCESS => self::LIE_DOWN,
        ],
        ActionEnum::GET_UP => [
            ActionOutputEnum::SUCCESS => self::GET_UP,
        ],
        ActionEnum::HIT => [
            ActionOutputEnum::SUCCESS => self::HIT_SUCCESS,
            ActionOutputEnum::FAIL => self::HIT_FAIL,
            ActionOutputEnum::CRITICAL_SUCCESS => self::HIT_CRITICAL_SUCCESS,
        ],
        ActionEnum::COMFORT => [
            ActionOutputEnum::SUCCESS => self::COMFORT_SUCCESS,
        ],
        ActionEnum::HEAL => [
            ActionOutputEnum::SUCCESS => self::HEAL_SUCCESS,
        ],
        ActionEnum::SELF_HEAL => [
            ActionOutputEnum::SUCCESS => self::SELF_HEAL,
        ],
        ActionEnum::ULTRAHEAL => [
            ActionOutputEnum::SUCCESS => self::ULTRAHEAL_SUCCESS,
        ],
        ActionEnum::USE_BANDAGE => [
            ActionOutputEnum::SUCCESS => self::SELF_HEAL,
        ],
        ActionEnum::SPREAD_FIRE => [
            ActionOutputEnum::SUCCESS => self::SPREAD_FIRE_SUCCESS,
        ],
        ActionEnum::INSTALL_CAMERA => [
            ActionOutputEnum::SUCCESS => self::INSTALL_CAMERA,
        ],
        ActionEnum::REMOVE_CAMERA => [
            ActionOutputEnum::SUCCESS => self::REMOVE_CAMERA,
        ],

        ActionEnum::STRENGTHEN_HULL => [
            ActionOutputEnum::SUCCESS => self::STRENGTHEN_SUCCESS,
            ActionOutputEnum::FAIL => self::DEFAULT_FAIL,
        ],

        ActionEnum::FLIRT => [
            ActionOutputEnum::SUCCESS => self::FLIRT_SUCCESS,
        ],

        ActionEnum::MOVE => [
            ActionOutputEnum::SUCCESS => self::EXIT_ROOM,
        ],

        ActionEnum::DO_THE_THING => [
            ActionOutputEnum::SUCCESS => self::DO_THE_THING_SUCCESS,
            ActionOutputEnum::FAIL => self::DO_THE_THING_BREAKS_SOFA,
        ],

        ActionEnum::CHECK_SPORE_LEVEL => [
            ActionOutputEnum::SUCCESS => self::CHECK_SPORE_LEVEL,
        ],

        ActionEnum::REMOVE_SPORE => [
            ActionOutputEnum::SUCCESS => self::REMOVE_SPORE_SUCCESS,
            ActionOutputEnum::FAIL => self::REMOVE_SPORE_FAIL,
        ],
        ActionEnum::PUBLIC_BROADCAST => [
            ActionOutputEnum::SUCCESS => self::PUBLIC_BROADCAST,
        ],
        ActionEnum::EXTINGUISH_MANUALLY => [
            ActionOutputEnum::SUCCESS => self::EXTINGUISH_SUCCESS,
            ActionOutputEnum::FAIL => self::EXTINGUISH_FAIL,
        ],
        ActionEnum::MOTIVATIONAL_SPEECH => [
            ActionOutputEnum::SUCCESS => self::MOTIVATIONAL_SPEECH,
        ],
        ActionEnum::BORING_SPEECH => [
            ActionOutputEnum::SUCCESS => self::BORING_SPEECH,
        ],
        ActionEnum::MAKE_SICK => [
            ActionOutputEnum::SUCCESS => self::MAKE_SICK,
        ],
        ActionEnum::FAKE_DISEASE => [
            ActionOutputEnum::SUCCESS => self::FAKE_DISEASE,
        ],
        ActionEnum::SURGERY => [
            ActionOutputEnum::FAIL => self::FAIL_SURGERY,
        ],
        ActionEnum::SELF_SURGERY => [
            ActionOutputEnum::FAIL => self::FAIL_SELF_SURGERY,
        ],
        ActionEnum::SCREW_TALKIE => [
            ActionOutputEnum::SUCCESS => self::SCREW_TALKIE_SUCCESS,
        ],
        ActionEnum::UPDATE_TALKIE => [
            ActionOutputEnum::SUCCESS => self::UPDATE_TALKIE_SUCCESS,
        ],
        ActionEnum::ATTACK => [
            ActionOutputEnum::SUCCESS => self::ATTACK_SUCCESS,
            ActionOutputEnum::FAIL => self::ATTACK_FAIL,
            ActionOutputEnum::CRITICAL_FAIL => self::ATTACK_CRITICAL_FAIL,
            ActionOutputEnum::CRITICAL_SUCCESS => self::ATTACK_CRITICAL_SUCCESS,
            ActionOutputEnum::ONE_SHOT => self::ATTACK_ONE_SHOT,
        ],
        ActionEnum::SHOOT => [
            ActionOutputEnum::SUCCESS => self::SHOOT_SUCCESS,
            ActionOutputEnum::FAIL => self::SHOOT_FAIL,
            ActionOutputEnum::CRITICAL_FAIL => self::SHOOT_CRITICAL_FAIL,
            ActionOutputEnum::CRITICAL_SUCCESS => self::SHOOT_CRITICAL_SUCCESS,
            ActionOutputEnum::ONE_SHOT => self::SHOOT_ONE_SHOT,
        ],
        ActionEnum::SUICIDE => [
            ActionOutputEnum::SUCCESS => self::SUICIDE_SUCCESS,
        ],
        ActionEnum::GAG => [
            ActionOutputEnum::SUCCESS => self::GAG_SUCCESS,
        ],
        ActionEnum::UNGAG => [
            ActionOutputEnum::SUCCESS => self::UNGAG_SUCCESS,
        ],
        ActionEnum::PLAY_ARCADE => [
            ActionOutputEnum::SUCCESS => self::PLAY_ARCADE_SUCCESS,
            ActionOutputEnum::FAIL => self::PLAY_ARCADE_FAIL,
        ],
        ActionEnum::SHOOT_HUNTER => [
            ActionOutputEnum::SUCCESS => self::SHOOT_HUNTER_SUCCESS,
            ActionOutputEnum::FAIL => self::SHOOT_HUNTER_FAIL,
        ],
        ActionEnum::SHOOT_RANDOM_HUNTER => [
            ActionOutputEnum::SUCCESS => self::SHOOT_HUNTER_SUCCESS,
            ActionOutputEnum::FAIL => self::SHOOT_HUNTER_FAIL,
        ],
        ActionEnum::ACCESS_TERMINAL => [
            ActionOutputEnum::SUCCESS => self::ACCESS_TERMINAL_SUCCESS,
        ],
        ActionEnum::TAKEOFF => [
            ActionOutputEnum::CRITICAL_SUCCESS => self::TAKEOFF_SUCCESS,
            ActionOutputEnum::SUCCESS => self::TAKEOFF_NO_PILOT,
        ],
        ActionEnum::LAND => [
            ActionOutputEnum::CRITICAL_SUCCESS => self::LAND_SUCCESS,
            ActionOutputEnum::SUCCESS => self::LAND_NO_PILOT,
        ],
        ActionEnum::SHOOT_HUNTER_PATROL_SHIP => [
            ActionOutputEnum::SUCCESS => self::SHOOT_HUNTER_PATROL_SHIP_SUCCESS,
            ActionOutputEnum::FAIL => self::SHOOT_HUNTER_PATROL_SHIP_FAIL,
        ],
        ActionEnum::SHOOT_RANDOM_HUNTER_PATROL_SHIP => [
            ActionOutputEnum::SUCCESS => self::SHOOT_HUNTER_PATROL_SHIP_SUCCESS,
            ActionOutputEnum::FAIL => self::SHOOT_HUNTER_PATROL_SHIP_FAIL,
        ],
        ActionEnum::RENOVATE => [
            ActionOutputEnum::SUCCESS => self::RENOVATE_SUCCESS,
            ActionOutputEnum::FAIL => self::RENOVATE_FAIL,
        ],
        ActionEnum::AUTO_EJECT => [
            ActionOutputEnum::SUCCESS => self::AUTO_EJECT_SUCCESS,
        ],
        ActionEnum::INSERT_FUEL_CHAMBER => [
            ActionOutputEnum::SUCCESS => self::INSERT_FUEL_CHAMBER_SUCCESS,
        ],
        ActionEnum::RETRIEVE_FUEL_CHAMBER => [
            ActionOutputEnum::SUCCESS => self::RETRIEVE_FUEL_CHAMBER_SUCCESS,
        ],
        ActionEnum::CHECK_FUEL_CHAMBER_LEVEL => [
            ActionOutputEnum::SUCCESS => self::CHECK_FUEL_CHAMBER_LEVEL_SUCCESS,
        ],
        ActionEnum::HACK => [
            ActionOutputEnum::SUCCESS => self::HACK_SUCCESS,
            ActionOutputEnum::FAIL => self::HACK_FAIL,
        ],
        ActionEnum::ADVANCE_DAEDALUS => [
            ActionOutputEnum::SUCCESS => self::ADVANCE_DAEDALUS_SUCCESS,
            ActionOutputEnum::FAIL => self::ADVANCE_DAEDALUS_FAIL,
            ActionOutputEnum::ARACK_PREVENTS_TRAVEL => self::ADVANCE_DAEDALUS_ARACK_PREVENTS_TRAVEL,
            ActionOutputEnum::NO_FUEL => self::ADVANCE_DAEDALUS_NO_FUEL,
        ],
        ActionEnum::SCAN => [
            ActionOutputEnum::SUCCESS => self::SCAN_SUCCESS,
            ActionOutputEnum::FAIL => self::SCAN_FAIL,
        ],
        ActionEnum::ANALYZE_PLANET => [
            ActionOutputEnum::SUCCESS => self::ANALYZE_PLANET_SUCCESS,
        ],
        ActionEnum::DELETE_PLANET => [
            ActionOutputEnum::SUCCESS => self::DELETE_PLANET_SUCCESS,
        ],
        ActionEnum::LEAVE_ORBIT => [
            ActionOutputEnum::SUCCESS => self::ADVANCE_DAEDALUS_SUCCESS,
            ActionOutputEnum::FAIL => self::ADVANCE_DAEDALUS_FAIL,
            ActionOutputEnum::ARACK_PREVENTS_TRAVEL => self::ADVANCE_DAEDALUS_ARACK_PREVENTS_TRAVEL,
            ActionOutputEnum::NO_FUEL => self::ADVANCE_DAEDALUS_NO_FUEL,
        ],
        ActionEnum::TAKEOFF_TO_PLANET => [
            ActionOutputEnum::SUCCESS => self::TAKEOFF_TO_PLANET_SUCCESS,
        ],
        ActionEnum::TAKEOFF_TO_PLANET_PATROL_SHIP => [
            ActionOutputEnum::SUCCESS => self::TAKEOFF_TO_PLANET_PATROL_SHIP_SUCCESS,
        ],
        ActionEnum::CHANGE_NERON_CPU_PRIORITY => [
            ActionOutputEnum::SUCCESS => self::CHANGE_NERON_CPU_PRIORITY_SUCCESS,
        ],
    ];
}
