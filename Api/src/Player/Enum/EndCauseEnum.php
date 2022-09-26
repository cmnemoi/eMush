<?php

namespace Mush\Player\Enum;

use Mush\Action\Enum\ActionEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Enum\StatusEnum;

class EndCauseEnum
{
    public const SOL_RETURN = 'sol_return';
    public const EDEN = 'eden';
    public const DAEDALUS_DESTROYED = 'daedalus_destroyed';
    public const KILLED_BY_NERON = 'killed_by_neron';
    public const SUPER_NOVA = 'super_nova';

    public const ALIEN_OBDUCTED = 'alien_obducted';

    public const ASSASSINATED = 'assassinated';
    public const DEPRESSION = 'depression';
    public const ASPHYXIA = 'asphyxia';
    public const ABANDONED = 'abandoned';
    public const ALERGY = 'alergy';
    public const SELF_EXTRACTED = 'self_extracted';
    public const EXPLORATION = 'exploration';
    public const EXPLORATION_COMBAT = 'exploration_combat';
    public const EXPLORATION_LOST = 'exploration_lost';
    public const ELECTROCUTED = 'electrocuted';
    public const INJURY = 'injury';
    public const BURNT = 'burnt';
    public const CLUMSINESS = 'clumsiness';
    public const SPACE_BATTLE = 'space_battle';
    public const SPACE_ASPHYXIED = 'space_asphyxied';
    public const BEHEADED = 'beheaded';
    public const STARVATION = 'starvation';
    public const QUARANTINE = 'quarantine';
    public const BLACK_BITE = 'black_bite';
    public const METAL_PLATE = 'metal_plate';
    public const ROCKETED = 'rocketed';
    public const BLED = 'bled';
    public const INFECTION = 'infection';
    public const MANKAROG = 'mankarog';

    public const NO_INFIRMERY = 'no_infirmerie'; // cause of death lost in a bug

    public const DEATH_CAUSE_MAP = [
        ActionEnum::HIT => self::ASSASSINATED,
        ActionEnum::SHOOT => self::ASSASSINATED,
        PlayerEvent::METAL_PLATE => self::METAL_PLATE,
        StatusEnum::FIRE => self::BURNT,
        ActionEnum::REMOVE_SPORE => self::SELF_EXTRACTED,
        ModifierScopeEnum::EVENT_CLUMSINESS => self::CLUMSINESS,
        // @TODO MORE DEATH REASON PER HEALTH POINT LOSS
    ];
}
