<?php

namespace Mush\Action\Enum;

use Doctrine\Common\Collections\ArrayCollection;

abstract class ActionEnum
{
    // Not yet an exhaustive list

    // admin actions
    public const string SUICIDE = 'suicide';
    public const string AUTO_DESTROY = 'auto_destroy';
    public const string KILL_PLAYER = 'kill_player';

    // alpha actions
    public const string REJUVENATE = 'rejuvenate';
    public const string FAKE_DISEASE = 'fake_disease';
    public const string UPDATE_TALKIE = 'update_talkie';

    // Permanent Item actions
    public const string MOVE = 'move';
    public const string TAKE = 'take';
    public const string HIDE = 'hide';
    public const string DROP = 'drop';
    public const string EXAMINE = 'examine';
    public const string REPAIR = 'repair';
    public const string REPORT_EQUIPMENT = 'report_equipment';
    public const string SEARCH = 'search';

    // Mush-only actions
    public const string EXTRACT_SPORE = 'extract_spore';
    public const string INFECT = 'infect';
    public const string SABOTAGE = 'sabotage';
    public const string GO_BERSERK = 'go_berserk';

    // Item-enabled actions
    public const string READ_DOCUMENT = 'read_document';
    public const string READ_BOOK = 'read_book';
    public const string SHRED = 'shred';
    public const string ATTACK = 'attack';
    public const string CONSUME = 'consume';
    public const string CONSUME_DRUG = 'consume_drug';
    public const string BUILD = 'build';
    public const string WATER_PLANT = 'water_plant';
    public const string TREAT_PLANT = 'treat_plant';
    public const string TRANSPLANT = 'transplant';
    public const string HYBRIDIZE = 'hybridize';
    public const string HACK = 'hack';
    public const string EXTINGUISH = 'extinguish';
    public const string GAG = 'gag';
    public const string HYPERFREEZE = 'hyperfreeze';
    public const string EXPRESS_COOK = 'express_cook';
    public const string STRENGTHEN_HULL = 'strengthen_hull';
    public const string PUBLIC_BROADCAST = 'public_broadcast';
    public const string ULTRAHEAL = 'ultraheal';
    public const string CURE = 'cure';
    public const string USE_BANDAGE = 'use_bandage';
    public const string TRY_KUBE = 'try_kube';
    public const string OPEN = 'open';
    public const string SHOOT = 'shoot';
    public const string HEAL = 'heal';
    public const string SELF_HEAL = 'self_heal';
    public const string RENOVATE = 'renovate';
    public const string AUTO_EJECT = 'auto_eject';

    // Equipment-enabled actions
    public const string INSERT_FUEL = 'insert_fuel';
    public const string INSERT_FUEL_CHAMBER = 'insert_fuel_chamber';
    public const string INSERT_OXYGEN = 'insert_oxygen';
    public const string RETRIEVE_FUEL = 'retrieve_fuel';
    public const string RETRIEVE_FUEL_CHAMBER = 'retrieve_fuel_chamber';
    public const string RETRIEVE_OXYGEN = 'retrieve_oxygen';
    public const string CHECK_FUEL_CHAMBER_LEVEL = 'check_fuel_chamber_level';
    public const string COOK = 'cook';
    public const string COFFEE = 'coffee';
    public const string SELF_SURGERY = 'self_surgery';
    public const string CHECK_INFECTION = 'check_infection';
    public const string SHOWER = 'shower';
    public const string WASH_IN_SINK = 'wash_in_sink';
    public const string CHECK_ROSTER = 'check_roster';
    public const string PLAY_ARCADE = 'play_arcade';
    public const string LIE_DOWN = 'lie_down';
    public const string DISPENSE = 'dispense';
    public const string SHOOT_HUNTER = 'shoot_hunter';
    public const string SHOOT_HUNTER_PATROL_SHIP = 'shoot_hunter_patrol_ship';
    public const string SHOOT_RANDOM_HUNTER = 'shoot_random_hunter';
    public const string SHOOT_RANDOM_HUNTER_PATROL_SHIP = 'shoot_random_hunter_patrol_ship';
    public const string ACCESS_TERMINAL = 'access_terminal';
    public const string CONTACT_SOL = 'contact_sol';
    public const string INSTALL_CAMERA = 'install_camera';
    public const string REMOVE_CAMERA = 'remove_camera';
    public const string CHECK_SPORE_LEVEL = 'check_spore_level';
    public const string REMOVE_SPORE = 'remove_spore';
    public const string TAKEOFF = 'takeoff';
    public const string LAND = 'land';
    public const string COLLECT_SCRAP = 'collect_scrap';
    public const string TAKEOFF_TO_PLANET = 'takeoff_to_planet';
    public const string TAKEOFF_TO_PLANET_PATROL_SHIP = 'takeoff_to_planet_patrol_ship';

    // Permanent Player Actions
    public const string UNGAG = 'ungag';
    public const string FLIRT = 'flirt';
    public const string GET_UP = 'get_up';
    public const string GUARD = 'guard';
    public const string HIT = 'hit';
    public const string WHISPER = 'whisper';
    public const string REPORT_FIRE = 'report_fire';
    public const string DO_THE_THING = 'do_the_thing';
    public const string CONVERT_ACTION_TO_MOVEMENT = 'convert_action_to_movement';

    // Skill-related actions (Humans)
    public const string FIERY_SPEECH = 'fiery_speech';
    public const string KIND_WORDS = 'kind_words';
    public const string COMFORT = 'comfort';
    public const string PATROL_CHANGE_STANCE = 'patrol_change_stance';
    public const string PUT_THROUGH_DOOR = 'put_through_door';
    public const string PRINT_ZE_LIST = 'print_ze_list';
    public const string PRINT_SECRET_LIST = 'print_secret_list';
    public const string SURGERY = 'surgery';
    public const string DISASSEMBLE = 'disassemble';
    public const string REINFORCE_EQUIPMENT = 'reinforce_equipment'; // /!\ This is preventing disassembly, not reinforcing the hull!
    public const string PREMONITION = 'premonition';
    public const string EXTINGUISH_MANUALLY = 'extinguish_manually';
    public const string CEASEFIRE = 'ceasefire';
    public const string TORTURE = 'torture';
    public const string GENIUS = 'become_genius';
    public const string PUTSCH = 'putsch';
    public const string RUN_HOME = 'run_home';
    public const string DAUNT = 'daunt';
    public const string ANATHEMA = 'anathema';
    public const string METALWORKER = 'gen_metal';
    public const string GEN_METAL = 'gen_metal';
    public const string MOTIVATIONAL_SPEECH = 'motivational_speech';
    public const string BORING_SPEECH = 'boring_speech';

    // Skill-related actions (Mush)
    public const string PHAGOCYTE = 'eat_spore';
    public const string EAT_SPORE = 'eat_spore';
    public const string FUNGAL_KITCHEN = 'mix_ration_spore';
    public const string MIX_RATION_SPORE = 'mix_ration_spore';
    public const string DEPRESS = 'depress';
    public const string TRAP_SHELF = 'trap_closet';
    public const string TRAP_CLOSET = 'trap_closet';
    public const string SLIME_OBJECT = 'slime_object';
    public const string EXCHANGE_BODY = 'exchange_body';
    public const string TRANSFER = 'exchange_body';
    public const string DOOR_SABOTAGE = 'door_sabotage';
    public const string DEFACE = 'deface';
    public const string DELOG = 'delog';
    public const string MAKE_SICK = 'make_sick';
    public const string SCREW_TALKIE = 'screw_talkie';
    public const string SPREAD_FIRE = 'spread_fire';
    public const string NERON_DEPRESS = 'neron_depress';
    public const string MASS_MUSHIFICATION = 'mass_mushification';
    public const string MASS_GGEDON = 'mass_ggeddon';

    // Terminal related actions
    public const string EXIT_TERMINAL = 'exit_terminal';
    public const string ADVANCE_DAEDALUS = 'advance_daedalus';
    public const string SCAN = 'scan';
    public const string ANALYZE_PLANET = 'analyze_planet';
    public const string TURN_DAEDALUS_LEFT = 'turn_daedalus_left';
    public const string TURN_DAEDALUS_RIGHT = 'turn_daedalus_right';
    public const string DELETE_PLANET = 'delete_planet';
    public const string LEAVE_ORBIT = 'leave_orbit';
    public const string WRITE = 'write';
    public const string CHANGE_NERON_CPU_PRIORITY = 'change_neron_cpu_priority';
    public const string REPAIR_PILGRED = 'repair_pilgred';
    public const string RETURN_TO_SOL = 'return_to_sol';
    public const string PARTICIPATE = 'participate';

    public static function getPermanentItemActions(): array
    {
        return [
            self::TAKE,
            self::DROP,
            self::HIDE,
        ];
    }

    public static function getPermanentEquipmentActions(): array
    {
        return [
            self::EXAMINE,
            self::REPAIR,
            self::REPORT_EQUIPMENT,
        ];
    }

    public static function getPermanentSelfActions(): array
    {
        return [
            self::UNGAG,
            self::GET_UP,
            self::GUARD,
            self::SEARCH,
            self::EXTRACT_SPORE,
            self::GO_BERSERK,
            self::REPORT_FIRE,
        ];
    }

    public static function getPermanentPlayerActions(): array
    {
        return [
            self::HIT,
            self::WHISPER,
            self::HEAL,
            self::SELF_HEAL,
        ];
    }

    public static function getForceGetUpActions(): array
    {
        return [
            self::HIT,
            self::ATTACK,
            self::SHOOT,
        ];
    }

    public static function getActionPointModifierProtectedActions(): array
    {
        return [
            self::REJUVENATE,
            self::SUICIDE,
            self::AUTO_DESTROY,
            self::KILL_PLAYER,
            self::EXIT_TERMINAL,
        ];
    }

    public static function getChangingRoomActions(): ArrayCollection
    {
        return new ArrayCollection([
            self::MOVE,
            self::LAND,
            self::TAKEOFF,
        ]);
    }

    public static function getChangingRoomPatrolshipActions(): ArrayCollection
    {
        return new ArrayCollection([
            self::LAND,
            self::TAKEOFF,
        ]);
    }

    public static function getTakeOffToPlanetActions(): ArrayCollection
    {
        return new ArrayCollection([
            self::TAKEOFF_TO_PLANET,
            self::TAKEOFF_TO_PLANET_PATROL_SHIP,
        ]);
    }

    public static function getAll(): ArrayCollection
    {
        $actions = new ArrayCollection();
        $reflectionClass = new \ReflectionClass(__CLASS__);
        $constants = $reflectionClass->getConstants();
        foreach ($constants as $constant) {
            $actions->add($constant);
        }

        return $actions;
    }
}
