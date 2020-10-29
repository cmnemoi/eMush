<?php

namespace Mush\Action\Enum;

class ActionEnum
{
    // Not yet an exhaustive list

    // Permanent Item actions
    public const MOVE = 'move';
    public const TAKE = 'take';
    public const HIDE = 'hide';
    public const DROP = 'drop';
    public const EXAMINE = 'examine';
    public const HIT = 'hit';
    public const REPAIR = 'repair';

    // Mush-only actions
    public const EXTRACT_SPORE = 'extract_spore';
    public const INFECT = 'infect';
    public const SABOTAGE = 'sabotage';
    public const GO_BERSERK = 'go_berserk';

    // Item-enabled actions
    public const ATTACK = 'attack';
    public const CONSUME = 'consume';

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
    public const EXTINGUISH = 'hand_extinguish';
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
    public const DEFACE = 'delog';
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
            self::EXAMINE,
            self::REPAIR
        ];
    }
}
