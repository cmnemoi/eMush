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
    case RESET_SKILL_POINTS = 'reset_skill_points';
    case TRIGGER_ALL_REBEL_CONTACTS = 'trigger_all_rebel_contacts';
    case CREATE_TRADE = 'create_trade';
    case FORCE_CYCLE_CHANGE = 'force_cycle_change';
    case FORCE_EXPLORATION_STEP = 'force_exploration_step';
    case REJUVENATE = 'rejuvenate';
    case FAKE_DISEASE = 'fake_disease';
    case RECHARGE_BATTERY = 'recharge_battery';

    // deprecated
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
    case CONVERT_CAT = 'convert_cat';

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
    case THROW_GRENADE = 'throw_grenade';
    case UPGRADE_DRONE_TO_TURBO = 'upgrade_drone_to_turbo';
    case UPGRADE_DRONE_TO_FIREFIGHTER = 'upgrade_drone_to_firefighter';
    case UPGRADE_DRONE_TO_PILOT = 'upgrade_drone_to_pilot';
    case UPGRADE_DRONE_TO_SENSOR = 'upgrade_drone_to_sensor';
    case TAKE_CAT = 'take_cat';
    case PET_CAT = 'pet_cat';
    case SHOOT_CAT = 'shoot_cat';
    case CURE_CAT = 'cure_cat';
    case OPEN_CONTAINER = 'open_container';
    case PLAY_WITH_DOG = 'play_with_dog';
    case LIE_DOWN_IN_SHIP = 'lie_down_in_ship';
    case READ_SCHOOLBOOKS = 'read_schoolbooks';

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
    case TAKE_SHOWER = 'take_shower';
    case WASH_IN_SINK = 'wash_in_sink';
    case WASH_WITH_PERFUME = 'wash_with_perfume';
    case CHECK_ROSTER = 'check_roster';
    case PLAY_ARCADE = 'play_arcade';
    case LIE_DOWN = 'lie_down';
    case DISPENSE = 'dispense';
    case SHOOT_HUNTER = 'shoot_hunter';
    case SHOOT_HUNTER_PATROL_SHIP = 'shoot_hunter_patrol_ship';
    case SHOOT_RANDOM_HUNTER = 'shoot_random_hunter';
    case SHOOT_RANDOM_HUNTER_PATROL_SHIP = 'shoot_random_hunter_patrol_ship';
    case ACCESS_TERMINAL = 'access_terminal';
    case BYPASS_TERMINAL = 'bypass_terminal';
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
    case ACCEPT_MISSION = 'accept_mission';
    case REJECT_MISSION = 'reject_mission';

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
    case REINFORCE = 'reinforce'; // /!\ This is preventing disassembly, not reinforcing the hull!
    case PREMONITION = 'premonition';
    case EXTINGUISH_MANUALLY = 'extinguish_manually';
    case CEASEFIRE = 'ceasefire';
    case TORTURE = 'torture';
    case BECOME_GENIUS = 'become_genius';
    case PUTSCH = 'putsch';
    case RUN_HOME = 'run_home';
    case DAUNT = 'daunt';
    case ANATHEMA = 'anathema';
    case GEN_METAL = 'gen_metal';
    case MOTIVATIONAL_SPEECH = 'motivational_speech';
    case BORING_SPEECH = 'boring_speech';
    case CHITCHAT = 'chitchat';
    case GRAFT = 'graft';
    case LEARN = 'learn';

    // Skill-related actions (Mush)
    case PHAGOCYTE = 'eat_spore';
    case MIX_RATION_SPORE = 'mix_ration_spore';
    case DEPRESS = 'depress';
    case TRAP_CLOSET = 'trap_closet';
    case SLIME_OBJECT = 'slime_object';
    case EXCHANGE_BODY = 'exchange_body';
    case DOOR_SABOTAGE = 'door_sabotage';
    case DELOG = 'delog';
    case MAKE_SICK = 'make_sick';
    case SCREW_TALKIE = 'screw_talkie';
    case SPREAD_FIRE = 'spread_fire';
    case NERON_DEPRESS = 'neron_depress';
    case MASS_GGEDON = 'mass_ggeddon';
    case SLIME_TRAP = 'slime_trap';
    case GIVE_NIGHTMARE = 'give_nightmare';
    case ADAPT_EPIGENETICS = 'adapt_epigenetics';
    case BECOME_ANONYMOUS = 'become_anonymous';
    case BECOME_ANONYMOUS_REVERSE = 'become_anonymous_reverse';
    case INSTALL_CAMERA_NIMBLE_FINGERS = 'install_camera_nimble_fingers';
    case REMOVE_CAMERA_NIMBLE_FINGERS = 'remove_camera_nimble_fingers';
    case SABOTAGE_EXPLORATION = 'sabotage_exploration';

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
    case CHANGE_NERON_CREW_LOCK = 'change_neron_crew_lock';
    case CHANGE_NERON_FOOD_DESTRUCTION_OPTION = 'change_neron_food_destruction_option';
    case TOGGLE_PLASMA_SHIELD = 'toggle_plasma_shield';
    case TOGGLE_MAGNETIC_NET = 'toggle_magnetic_net';
    case TOGGLE_NERON_INHIBITION = 'toggle_neron_inhibition';
    case TOGGLE_VOCODED_ANNOUNCEMENTS = 'toggle_vocoded_announcements';
    case TOGGLE_DEATH_ANNOUNCEMENTS = 'toggle_death_announcements';
    case DECODE_REBEL_SIGNAL = 'decode_rebel_signal';
    case PARTICIPATE_RESEARCH = 'participate_research';
    case COMPUTE_EDEN = 'compute_eden';
    case TRAVEL_TO_EDEN = 'travel_to_eden';
    case ESTABLISH_LINK_WITH_SOL = 'establish_link_with_sol';
    case UPGRADE_NERON = 'upgrade_neron';
    case CONTACT_XYLOPH = 'contact_xyloph';
    case ACCEPT_TRADE = 'accept_trade';
    case REFUSE_TRADE = 'refuse_trade';

    // Titles-related actions
    case COMMANDER_ORDER = 'commander_order';
    case COM_MANAGER_ANNOUNCEMENT = 'com_manager_announcement';

    // Statuses-related actions

    case REMOVE_BEGINNER = 'remove_beginner';

    public function toString(): string
    {
        return $this->value;
    }

    public function isDetectedByMycoAlarm(): bool
    {
        return \in_array($this, [
            self::CONVERT_CAT,
            self::EXCHANGE_BODY,
            self::GIVE_NIGHTMARE,
            self::GO_BERSERK,
            self::INFECT,
            self::MAKE_SICK,
            self::MIX_RATION_SPORE,
            self::SCREW_TALKIE,
            self::TRAP_CLOSET,
        ], true);
    }

    public static function getForceGetUpActions(): array
    {
        return [
            self::HIT->value,
            self::ATTACK->value,
            self::SHOOT->value,
        ];
    }

    public static function getDoesNotForceAGetUpActions(): array
    {
        return [
            self::GET_UP->value,
            self::BECOME_ANONYMOUS->value,
            self::BECOME_ANONYMOUS_REVERSE->value,
        ];
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

    public static function getActionsWhichAlwaysTriggerRoomTraps(): ArrayCollection
    {
        return new ArrayCollection([
            self::EXTINGUISH,
            self::DO_THE_THING,
            self::SEARCH,
            self::STRENGTHEN_HULL,
            self::REPAIR_PILGRED,
            self::PARTICIPATE,
            self::SHOOT_HUNTER,
            self::GET_UP,
            self::SPREAD_FIRE,
            self::DOOR_SABOTAGE,
            self::GEN_METAL,
            self::HIDE,
            self::INSTALL_CAMERA,
            self::INSTALL_CAMERA_NIMBLE_FINGERS,
            self::INSERT_FUEL_CHAMBER,
            self::EXTINGUISH_MANUALLY,
            self::REPORT_FIRE,
        ]);
    }

    public function isCommsAction(): bool
    {
        return \in_array($this, [
            self::CONTACT_XYLOPH,
            self::DECODE_REBEL_SIGNAL,
            self::UPGRADE_NERON,
            self::ESTABLISH_LINK_WITH_SOL,
        ], true);
    }

    public static function getAllAsStrings(): ArrayCollection
    {
        $actions = new ArrayCollection();
        $reflectionClass = new \ReflectionClass(__CLASS__);
        $constants = $reflectionClass->getConstants();
        foreach ($constants as $constant) {
            $actions->add($constant->value);
        }

        return $actions;
    }
}
