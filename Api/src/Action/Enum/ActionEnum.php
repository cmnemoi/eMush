<?php

namespace Mush\Action\Enum;

class ActionEnum
{
    // Not yet an exhaustive list

    //alpha actions
    public const REJUVENATE_ALPHA = 'rejuvenate_alpha';

    // Permanent Item actions
    public const MOVE = 'move';
    public const TAKE = 'take';
    public const HIDE = 'hide';
    public const DROP = 'drop';
    public const EXAMINE = 'examine';
    public const REPAIR = 'repair';
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
    public const STRENGTHEN = 'strengthen';
    public const WRITE = 'write';
    public const PUBLIC_BROADCAST = 'public_broadcast';
    public const HEAL = 'heal';
    public const SELF_HEAL = 'self_heal';
    public const ULTRAHEAL = 'ultraheal';
    public const CURE = 'cure';
    public const USE_BANDAGE = 'use_bandage';
    public const TRY_THE_KUBE = 'try_the_kube';
    public const OPEN = 'open';

    // Item-enabled actions
    public const INSERT_FUEL = 'insert_fuel';
    public const INSERT_FUEL_CHAMBER = 'insert_fuel_chamber';
    public const INSERT_OXYGEN = 'insert_oxygen';
    public const RETRIEVE_FUEL = 'retrieve_fuel';
    public const RETRIEVE_FUEL_CHAMBER = 'retrieve_fuel_chamber';
    public const RETRIEVE_OXYGEN = 'retrieve_oxygen';
    public const COOK = 'cook';
    public const COFFEE = 'coffee';
    public const SELF_SURGERY = 'self_surgery';
    public const CHECK_INFECTION = 'check_infection';
    public const SHOWER = 'shower';
    public const CHECK_ROSTER = 'check_roster';
    public const PLAY_ARCADE = 'play_arcade';
    public const LIE_DOWN = 'lie_down';
    public const DISPENSE = 'dispense';
    public const SHOOT_HUNTER = 'shoot_hunter';
    public const ACCES_TERMINAL = 'acces_terminal';

    // Permanent Player Actions
    public const UNGAG = 'ungag';
    public const GET_UP = 'get_up';
    public const GUARD = 'guard';
    public const HIT = 'hit';
    public const WHISPER = 'whisper';

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
    public const REINFORCE = 'reinforce'; // /!\ This is preventing disassembly, not reinforcing the hull!
    public const PREMONOTION = 'premonition';
    public const HAND_EXTINGUISH = 'hand_extinguish';
    public const CEASE_FIRE = 'cease_fire';
    public const CEASEFIRE = 'ceasefire';
    public const TORTURE = 'torture';
    public const GENIUS = 'become_genius';
    public const PUTSCH = 'putsch';
    public const RUN_HOME = 'run_home';
    public const DAUNT = 'daunt';
    public const ANATHEM = 'anathem';
    public const METALWORKER = 'gen_metal';
    public const GEN_METAL = 'gen_metal';

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
    public const GIVE_DISEASE = 'give_disease';
    public const SCREW_TALKY = 'screw_talky';
    public const SPREAD_FIRE = 'spread_fire';
    public const NERON_DEPRESS = 'neron_depress';
    public const MASS_MUSHIFICATION = 'mass_ggeddon';
    public const MASS_GGEDON = 'mass_ggeddon';

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
        ];
    }

    public static function getPermanentPlayerActions(): array
    {
        return [
            self::HIT,
            self::WHISPER,
        ];
    }
}
