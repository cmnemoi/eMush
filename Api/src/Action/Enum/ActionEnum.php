<?php

namespace Mush\Action\Enum;

use Doctrine\Common\Collections\ArrayCollection;

class ActionEnum
{
    // Not yet an exhaustive list

    // admin actions
    public const SUICIDE = 'suicide';
    public const AUTO_DESTROY = 'auto_destroy';
    public const KILL_PLAYER = 'kill_player';

    // alpha actions
    public const REJUVENATE = 'rejuvenate';
    public const FAKE_DISEASE = 'fake_disease';
    public const UPDATE_TALKIE = 'update_talkie';

    // Permanent Item actions
    public const MOVE = 'move';
    public const TAKE = 'take';
    public const HIDE = 'hide';
    public const DROP = 'drop';
    public const EXAMINE = 'examine';
    public const REPAIR = 'repair';
    public const REPORT_EQUIPMENT = 'report_equipment';
    public const SEARCH = 'search';

    // Mush-only actions
    public const EXTRACT_SPORE = 'extract_spore';
    public const INFECT = 'infect';
    public const SABOTAGE = 'sabotage';
    public const GO_BERSERK = 'go_berserk';

    // Item-enabled actions
    public const READ_DOCUMENT = 'read_document';
    public const READ_BOOK = 'read_book';
    public const SHRED = 'shred';
    public const ATTACK = 'attack';
    public const CONSUME = 'consume';
    public const CONSUME_DRUG = 'consume_drug';
    public const BUILD = 'build';
    public const WATER_PLANT = 'water_plant';
    public const TREAT_PLANT = 'treat_plant';
    public const TRANSPLANT = 'transplant';
    public const HYBRIDIZE = 'hybridize';
    public const HACK = 'hack';
    public const EXTINGUISH = 'extinguish';
    public const GAG = 'gag';
    public const HYPERFREEZE = 'hyperfreeze';
    public const EXPRESS_COOK = 'express_cook';
    public const STRENGTHEN_HULL = 'strengthen_hull';
    public const PUBLIC_BROADCAST = 'public_broadcast';
    public const ULTRAHEAL = 'ultraheal';
    public const CURE = 'cure';
    public const USE_BANDAGE = 'use_bandage';
    public const TRY_KUBE = 'try_kube';
    public const OPEN = 'open';
    public const SHOOT = 'shoot';
    public const HEAL = 'heal';
    public const SELF_HEAL = 'self_heal';
    public const RENOVATE = 'renovate';
    public const AUTO_EJECT = 'auto_eject';

    // Equipment-enabled actions
    public const INSERT_FUEL = 'insert_fuel';
    public const INSERT_FUEL_CHAMBER = 'insert_fuel_chamber';
    public const INSERT_OXYGEN = 'insert_oxygen';
    public const RETRIEVE_FUEL = 'retrieve_fuel';
    public const RETRIEVE_FUEL_CHAMBER = 'retrieve_fuel_chamber';
    public const RETRIEVE_OXYGEN = 'retrieve_oxygen';
    public const CHECK_FUEL_CHAMBER_LEVEL = 'check_fuel_chamber_level';
    public const COOK = 'cook';
    public const COFFEE = 'coffee';
    public const SELF_SURGERY = 'self_surgery';
    public const CHECK_INFECTION = 'check_infection';
    public const SHOWER = 'shower';
    public const WASH_IN_SINK = 'wash_in_sink';
    public const CHECK_ROSTER = 'check_roster';
    public const PLAY_ARCADE = 'play_arcade';
    public const LIE_DOWN = 'lie_down';
    public const DISPENSE = 'dispense';
    public const SHOOT_HUNTER = 'shoot_hunter';
    public const SHOOT_HUNTER_PATROL_SHIP = 'shoot_hunter_patrol_ship';
    public const SHOOT_RANDOM_HUNTER = 'shoot_random_hunter';
    public const SHOOT_RANDOM_HUNTER_PATROL_SHIP = 'shoot_random_hunter_patrol_ship';
    public const ACCESS_TERMINAL = 'access_terminal';
    public const CONTACT_SOL = 'contact_sol';
    public const INSTALL_CAMERA = 'install_camera';
    public const REMOVE_CAMERA = 'remove_camera';
    public const CHECK_SPORE_LEVEL = 'check_spore_level';
    public const REMOVE_SPORE = 'remove_spore';
    public const TAKEOFF = 'takeoff';
    public const LAND = 'land';
    public const COLLECT_SCRAP = 'collect_scrap';
    public const TAKEOFF_TO_PLANET = 'takeoff_to_planet';
    public const TAKEOFF_TO_PLANET_PATROL_SHIP = 'takeoff_to_planet_patrol_ship';

    // Permanent Player Actions
    public const UNGAG = 'ungag';
    public const FLIRT = 'flirt';
    public const GET_UP = 'get_up';
    public const GUARD = 'guard';
    public const HIT = 'hit';
    public const WHISPER = 'whisper';
    public const REPORT_FIRE = 'report_fire';
    public const DO_THE_THING = 'do_the_thing';
    public const CONVERT_ACTION_TO_MOVEMENT = 'convert_action_to_movement';

    // Skill-related actions (Humans)
    public const FIERY_SPEECH = 'fiery_speech';
    public const KIND_WORDS = 'kind_words';
    public const COMFORT = 'comfort';
    public const PATROL_CHANGE_STANCE = 'patrol_change_stance';
    public const PUT_THROUGH_DOOR = 'put_through_door';
    public const PRINT_ZE_LIST = 'print_ze_list';
    public const PRINT_SECRET_LIST = 'print_secret_list';
    public const SURGERY = 'surgery';
    public const DISASSEMBLE = 'disassemble';
    public const REINFORCE_EQUIPMENT = 'reinforce_equipment'; // /!\ This is preventing disassembly, not reinforcing the hull!
    public const PREMONITION = 'premonition';
    public const EXTINGUISH_MANUALLY = 'extinguish_manually';
    public const CEASEFIRE = 'ceasefire';
    public const TORTURE = 'torture';
    public const GENIUS = 'become_genius';
    public const PUTSCH = 'putsch';
    public const RUN_HOME = 'run_home';
    public const DAUNT = 'daunt';
    public const ANATHEMA = 'anathema';
    public const METALWORKER = 'gen_metal';
    public const GEN_METAL = 'gen_metal';
    public const MOTIVATIONAL_SPEECH = 'motivational_speech';
    public const BORING_SPEECH = 'boring_speech';

    // Skill-related actions (Mush)
    public const PHAGOCYTE = 'eat_spore';
    public const EAT_SPORE = 'eat_spore';
    public const FUNGAL_KITCHEN = 'mix_ration_spore';
    public const MIX_RATION_SPORE = 'mix_ration_spore';
    public const DEPRESS = 'depress';
    public const TRAP_SHELF = 'trap_closet';
    public const TRAP_CLOSET = 'trap_closet';
    public const SLIME_OBJECT = 'slime_object';
    public const EXCHANGE_BODY = 'exchange_body';
    public const TRANSFER = 'exchange_body';
    public const DOOR_SABOTAGE = 'door_sabotage';
    public const DEFACE = 'deface';
    public const DELOG = 'delog';
    public const MAKE_SICK = 'make_sick';
    public const SCREW_TALKIE = 'screw_talkie';
    public const SPREAD_FIRE = 'spread_fire';
    public const NERON_DEPRESS = 'neron_depress';
    public const MASS_MUSHIFICATION = 'mass_mushification';
    public const MASS_GGEDON = 'mass_ggeddon';

    // Terminal related actions
    public const EXIT_TERMINAL = 'exit_terminal';
    public const ADVANCE_DAEDALUS = 'advance_daedalus';
    public const SCAN = 'scan';
    public const ANALYZE_PLANET = 'analyze_planet';
    public const TURN_DAEDALUS_LEFT = 'turn_daedalus_left';
    public const TURN_DAEDALUS_RIGHT = 'turn_daedalus_right';
    public const DELETE_PLANET = 'delete_planet';
    public const LEAVE_ORBIT = 'leave_orbit';
    public const WRITE = 'write';

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
