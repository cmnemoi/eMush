<?php

namespace Mush\Action\Enum;

use Doctrine\Common\Collections\ArrayCollection;

enum ActionEnum: string
{
    // Not yet an exhaustive list

    // admin actions
    case SUICIDE = 'suicide';
    case AUTO_DESTROY = 'auto_destroy';
    case KILL_PLAYER = 'kill_player';
    case RESET_SPECIALIST_POINTS = 'reset_specialist_points';

    // alpha actions
    case REJUVENATE = 'rejuvenate';
    case FAKE_DISEASE = 'fake_disease';
    case UPDATE_TALKIE = 'update_talkie';

    // Permanent Item actions
    case MOVE = 'move';
    case TAKE = 'take';
    case HIDE = 'hide';
    case DROP = 'drop';
    case EXAMINE = 'examine';
    case REPAIR = 'repair';
    case REPORT_EQUIPMENT = 'report_equipment';
    case SEARCH = 'search';

    // Mush-only actions
    case EXTRACT_SPORE = 'extract_spore';
    case INFECT = 'infect';
    case SABOTAGE = 'sabotage';
    case GO_BERSERK = 'go_berserk';

    // Item-enabled actions
    case READ_DOCUMENT = 'read_document';
    case READ_BOOK = 'read_book';
    case SHRED = 'shred';
    case ATTACK = 'attack';
    case CONSUME = 'consume';
    case CONSUME_DRUG = 'consume_drug';
    case BUILD = 'build';
    case WATER_PLANT = 'water_plant';
    case TREAT_PLANT = 'treat_plant';
    case TRANSPLANT = 'transplant';
    case HYBRIDIZE = 'hybridize';
    case HACK = 'hack';
    case EXTINGUISH = 'extinguish';
    case GAG = 'gag';
    case HYPERFREEZE = 'hyperfreeze';
    case EXPRESS_COOK = 'express_cook';
    case STRENGTHEN_HULL = 'strengthen_hull';
    case PUBLIC_BROADCAST = 'public_broadcast';
    case ULTRAHEAL = 'ultraheal';
    case CURE = 'cure';
    case USE_BANDAGE = 'use_bandage';
    case TRY_KUBE = 'try_kube';
    case OPEN = 'open';
    case SHOOT = 'shoot';
    case HEAL = 'heal';
    case SELF_HEAL = 'self_heal';
    case RENOVATE = 'renovate';
    case AUTO_EJECT = 'auto_eject';

    // Equipment-enabled actions
    case INSERT_FUEL = 'insert_fuel';
    case INSERT_FUEL_CHAMBER = 'insert_fuel_chamber';
    case INSERT_OXYGEN = 'insert_oxygen';
    case RETRIEVE_FUEL = 'retrieve_fuel';
    case RETRIEVE_FUEL_CHAMBER = 'retrieve_fuel_chamber';
    case RETRIEVE_OXYGEN = 'retrieve_oxygen';
    case CHECK_FUEL_CHAMBER_LEVEL = 'check_fuel_chamber_level';
    case COOK = 'cook';
    case COFFEE = 'coffee';
    case SELF_SURGERY = 'self_surgery';
    case CHECK_INFECTION = 'check_infection';
    case SHOWER = 'shower';
    case WASH_IN_SINK = 'wash_in_sink';
    case CHECK_ROSTER = 'check_roster';
    case PLAY_ARCADE = 'play_arcade';
    case LIE_DOWN = 'lie_down';
    case DISPENSE = 'dispense';
    case SHOOT_HUNTER = 'shoot_hunter';
    case SHOOT_HUNTER_PATROL_SHIP = 'shoot_hunter_patrol_ship';
    case SHOOT_RANDOM_HUNTER = 'shoot_random_hunter';
    case SHOOT_RANDOM_HUNTER_PATROL_SHIP = 'shoot_random_hunter_patrol_ship';
    case ACCESS_TERMINAL = 'access_terminal';
    case CONTACT_SOL = 'contact_sol';
    case INSTALL_CAMERA = 'install_camera';
    case REMOVE_CAMERA = 'remove_camera';
    case CHECK_SPORE_LEVEL = 'check_spore_level';
    case REMOVE_SPORE = 'remove_spore';
    case TAKEOFF = 'takeoff';
    case LAND = 'land';
    case COLLECT_SCRAP = 'collect_scrap';
    case TAKEOFF_TO_PLANET = 'takeoff_to_planet';
    case TAKEOFF_TO_PLANET_PATROL_SHIP = 'takeoff_to_planet_patrol_ship';

    // Permanent Player Actions
    case UNGAG = 'ungag';
    case FLIRT = 'flirt';
    case GET_UP = 'get_up';
    case GUARD = 'guard';
    case HIT = 'hit';
    case WHISPER = 'whisper';
    case REPORT_FIRE = 'report_fire';
    case DO_THE_THING = 'do_the_thing';
    case CONVERT_ACTION_TO_MOVEMENT = 'convert_action_to_movement';

    // Skill-related actions (Humans)
    case FIERY_SPEECH = 'fiery_speech';
    case KIND_WORDS = 'kind_words';
    case COMFORT = 'comfort';
    case PATROL_CHANGE_STANCE = 'patrol_change_stance';
    case PUT_THROUGH_DOOR = 'put_through_door';
    case PRINT_ZE_LIST = 'print_ze_list';
    case PRINT_SECRET_LIST = 'print_secret_list';
    case SURGERY = 'surgery';
    case DISASSEMBLE = 'disassemble';
    case REINFORCE_EQUIPMENT = 'reinforce_equipment'; // /!\ This is preventing disassembly, not reinforcing the hull!
    case PREMONITION = 'premonition';
    case EXTINGUISH_MANUALLY = 'extinguish_manually';
    case CEASEFIRE = 'ceasefire';
    case TORTURE = 'torture';
    case GENIUS = 'become_genius';
    case PUTSCH = 'putsch';
    case RUN_HOME = 'run_home';
    case DAUNT = 'daunt';
    case ANATHEMA = 'anathema';
    case GEN_METAL = 'gen_metal';
    case MOTIVATIONAL_SPEECH = 'motivational_speech';
    case BORING_SPEECH = 'boring_speech';

    // Skill-related actions (Mush)
    case PHAGOCYTE = 'eat_spore';
    case FUNGAL_KITCHEN = 'mix_ration_spore';
    case DEPRESS = 'depress';
    case TRAP_SHELF = 'trap_closet';
    case SLIME_OBJECT = 'slime_object';
    case EXCHANGE_BODY = 'exchange_body';
    case DOOR_SABOTAGE = 'door_sabotage';
    case DEFACE = 'deface';
    case DELOG = 'delog';
    case MAKE_SICK = 'make_sick';
    case SCREW_TALKIE = 'screw_talkie';
    case SPREAD_FIRE = 'spread_fire';
    case NERON_DEPRESS = 'neron_depress';
    case MASS_MUSHIFICATION = 'mass_mushification';
    case MASS_GGEDON = 'mass_ggeddon';

    // Terminal related actions
    case EXIT_TERMINAL = 'exit_terminal';
    case ADVANCE_DAEDALUS = 'advance_daedalus';
    case SCAN = 'scan';
    case ANALYZE_PLANET = 'analyze_planet';
    case TURN_DAEDALUS_LEFT = 'turn_daedalus_left';
    case TURN_DAEDALUS_RIGHT = 'turn_daedalus_right';
    case DELETE_PLANET = 'delete_planet';
    case LEAVE_ORBIT = 'leave_orbit';
    case WRITE = 'write';
    case CHANGE_NERON_CPU_PRIORITY = 'change_neron_cpu_priority';
    case REPAIR_PILGRED = 'repair_pilgred';
    case RETURN_TO_SOL = 'return_to_sol';
    case PARTICIPATE = 'participate';

    public static function getPermanentItemActions(): array
    {
        return [
            self::TAKE->value,
            self::DROP->value,
            self::HIDE->value,
        ];
    }

    public static function getPermanentEquipmentActions(): array
    {
        return [
            self::EXAMINE->value,
            self::REPAIR->value,
            self::REPORT_EQUIPMENT->value,
        ];
    }

    public static function getPermanentSelfActions(): array
    {
        return [
            self::UNGAG->value,
            self::GET_UP->value,
            self::GUARD->value,
            self::SEARCH->value,
            self::EXTRACT_SPORE->value,
            self::GO_BERSERK->value,
            self::REPORT_FIRE->value,
        ];
    }

    public static function getPermanentPlayerActions(): array
    {
        return [
            self::HIT->value,
            self::WHISPER->value,
            self::HEAL->value,
            self::SELF_HEAL->value,
        ];
    }

    public static function getForceGetUpActions(): array
    {
        return [
            self::HIT->value,
            self::ATTACK->value,
            self::SHOOT->value,
        ];
    }

    public static function getActionPointModifierProtectedActions(): array
    {
        return [
            self::REJUVENATE->value,
            self::SUICIDE->value,
            self::AUTO_DESTROY->value,
            self::RESET_SPECIALIST_POINTS->value,
            self::KILL_PLAYER->value,
            self::EXIT_TERMINAL->value,
        ];
    }

    public static function getChangingRoomActions(): ArrayCollection
    {
        return new ArrayCollection([
            self::MOVE->value,
            self::LAND->value,
            self::TAKEOFF->value,
        ]);
    }

    public static function getChangingRoomPatrolshipActions(): ArrayCollection
    {
        return new ArrayCollection([
            self::LAND->value,
            self::TAKEOFF->value,
        ]);
    }

    public static function getTakeOffToPlanetActions(): ArrayCollection
    {
        return new ArrayCollection([
            self::TAKEOFF_TO_PLANET->value,
            self::TAKEOFF_TO_PLANET_PATROL_SHIP->value,
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
