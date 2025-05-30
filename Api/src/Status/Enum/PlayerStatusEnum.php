<?php

namespace Mush\Status\Enum;

use Mush\Game\Enum\TitleEnum;

abstract class PlayerStatusEnum
{
    public const array TITLES_OPPORTUNIST_STATUSES_MAP = [
        TitleEnum::COMMANDER => self::HAS_USED_OPPORTUNIST_AS_COMMANDER,
        TitleEnum::NERON_MANAGER => self::HAS_USED_OPPORTUNIST_AS_NERON_MANAGER,
        TitleEnum::COM_MANAGER => self::HAS_USED_OPPORTUNIST_AS_COM_MANAGER,
    ];
    public const string ANTISOCIAL = 'antisocial';
    public const string BERZERK = 'berzerk';
    public const string BRAINSYNC = 'brainsync';
    public const string BURDENED = 'burdened';
    public const string DEMORALIZED = 'demoralized';
    public const string DID_THE_THING = 'did_the_thing';
    public const string DID_BORING_SPEECH = 'did_boring_speech';
    public const string DIRTY = 'dirty';
    public const string DISABLED = 'disabled';
    public const string DRUG_EATEN = 'drug_eaten';
    public const string EUREKA_MOMENT = 'eureka_moment';
    public const string FIRST_TIME = 'first_time';
    public const string FOCUSED = 'focused';
    public const string FULL_STOMACH = 'full_stomach';
    public const string GAGGED = 'gagged';
    public const string GERMAPHOBE = 'germaphobe';
    public const string GUARDIAN = 'guardian';
    public const string HIGHLY_INACTIVE = 'highly_inactive';
    public const string HYPERACTIVE = 'hyperactive';
    public const string IMMUNIZED = 'immunized';
    public const string INACTIVE = 'inactive';
    public const string LOST = 'lost';
    public const string LYING_DOWN = 'lying_down';
    public const string MULTI_TEAMSTER = 'multi_teamster';
    public const string MUSH = 'mush';
    public const string OUTCAST = 'outcast';
    public const string PACIFIST = 'pacifist';
    public const string PREGNANT = 'pregnant';
    public const string SPORES = 'spores';
    public const string STARVING = 'starving';
    public const string STARVING_WARNING = 'starving_warning';
    public const string STUCK_IN_THE_SHIP = 'stuck_in_the_ship';
    public const string SUICIDAL = 'suicidal';
    public const string WATCHED_PUBLIC_BROADCAST = 'WATCHED_PUBLIC_BROADCAST';
    public const string TALKIE_SCREWED = 'talkie_screwed';
    public const string ALREADY_WASHED_IN_THE_SINK = 'already_washed_in_the_sink';
    public const string HAS_REJUVENATED = 'has_rejuvenated';
    public const string CHANGED_CPU_PRIORITY = 'changed_cpu_priority';
    public const string HAS_CHITCHATTED = 'has_chitchatted';
    public const string HAS_LEARNED_SKILL = 'has_learned_skill';
    public const string GENIUS_IDEA = 'genius_idea';
    public const string HAS_USED_GENIUS = 'has_used_genius';
    public const string HAS_CEASEFIRED = 'has_ceasefired';
    public const string PREVIOUS_ROOM = 'previous_room';
    public const string HAS_EXCHANGED_BODY = 'has_exchanged_body';
    public const string HAS_ISSUED_MISSION = 'has_issued_mission';
    public const string HAS_USED_MASS_GGEDON = 'has_used_mass_ggedon';
    public const string HAS_USED_DELOG = 'has_used_delog';
    public const string HAS_USED_PUTSCH = 'has_used_putsch';
    public const string PARIAH = 'pariah';
    public const string SLIME_TRAP = 'slime_trap';
    public const string HAS_READ_MAGE_BOOK = 'has_read_mage_book';
    public const string HAS_USED_OPPORTUNIST_AS_COMMANDER = 'has_used_opportunist_as_commander';
    public const string HAS_USED_OPPORTUNIST_AS_NERON_MANAGER = 'has_used_opportunist_as_neron_manager';
    public const string HAS_USED_OPPORTUNIST_AS_COM_MANAGER = 'has_used_opportunist_as_com_manager';
    public const string CAT_OWNER = 'cat_owner';
    public const string HAS_PETTED_CAT = 'has_petted_cat';
    public const string ANTIQUE_PERFUME_IMMUNIZED = 'antique_perfume_immunized';
    public const string HAS_DAUNTED = 'has_daunted';
    public const string HAS_GEN_METAL = 'has_gen_metal';
    public const string HAS_SABOTAGED_DOOR = 'has_sabotaged_door';
    public const string FITFUL_SLEEP = 'fitful_sleep';
    public const string CONTACTED_SOL_TODAY = 'contacted_sol_today';
    public const string BEGINNER = 'beginner';
    public const string SELECTED_FOR_STEEL_PLATE = 'selected_for_steel_plate';
    public const string SELECTED_FOR_BOARD_DISEASE = 'selected_for_board_disease';
    public const string SELECTED_FOR_ANXIETY_ATTACK = 'selected_for_anxiety_attack';
    public const string POINTLESS_PLAYER = 'pointless_player';
}
