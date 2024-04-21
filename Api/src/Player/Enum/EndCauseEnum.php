<?php

namespace Mush\Player\Enum;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Hunter\Event\HunterEvent;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Enum\StatusEnum;

class EndCauseEnum
{
    public const STILL_LIVING = 'still_living';

    // admin only
    public const SUICIDE = 'suicide';

    public const SOL_RETURN = 'sol_return';
    public const EDEN = 'eden';
    public const DAEDALUS_DESTROYED = 'daedalus_destroyed';
    public const KILLED_BY_NERON = 'killed_by_neron';
    public const SUPER_NOVA = 'super_nova';

    public const ALIEN_ABDUCTED = 'alien_abducted';

    public const ASSASSINATED = 'assassinated';
    public const DEPRESSION = 'depression';
    public const ASPHYXIA = 'asphyxia';
    public const ABANDONED = 'abandoned';
    public const ALLERGY = 'allergy';
    public const SELF_EXTRACTED = 'self_extracted';
    public const EXPLORATION = 'exploration';
    public const EXPLORATION_COMBAT = 'exploration_combat';
    public const EXPLORATION_LOST = 'exploration_lost';
    public const ELECTROCUTED = 'electrocuted';
    public const INJURY = 'injury';
    public const BURNT = 'burnt';
    public const CLUMSINESS = 'clumsiness';
    public const SPACE_BATTLE = 'space_battle';
    public const SPACE_ASPHYXIATED = 'space_asphyxiated';
    public const BEHEADED = 'beheaded';
    public const STARVATION = 'starvation';
    public const QUARANTINE = 'quarantine';
    public const BLACK_BITE = 'black_bite';
    public const METAL_PLATE = 'metal_plate';
    public const ROCKETED = 'rocketed';
    public const BLED = 'bled';
    public const INFECTION = 'infection';
    public const MANKAROG = 'mankarog';
    public const PATROL_SHIP_EXPLOSION = 'patrol_ship_explosion';

    public const NO_INFIRMERIE = 'no_infirmerie'; // cause of death lost in a bug

    public const DEATH_CAUSE_MAP = [
        self::STILL_LIVING => self::STILL_LIVING,
        self::SUICIDE => self::SUICIDE,
        self::SOL_RETURN => self::SOL_RETURN,
        self::EDEN => self::EDEN,
        self::DAEDALUS_DESTROYED => self::DAEDALUS_DESTROYED,
        self::KILLED_BY_NERON => self::KILLED_BY_NERON,
        self::SUPER_NOVA => self::SUPER_NOVA,
        self::ALIEN_ABDUCTED => self::ALIEN_ABDUCTED,
        self::ASSASSINATED => self::ASSASSINATED,
        self::DEPRESSION => self::DEPRESSION,
        self::ASPHYXIA => self::ASPHYXIA,
        self::ABANDONED => self::ABANDONED,
        self::ALLERGY => self::ALLERGY,
        self::SELF_EXTRACTED => self::SELF_EXTRACTED,
        self::EXPLORATION => self::EXPLORATION,
        self::EXPLORATION_COMBAT => self::EXPLORATION_COMBAT,
        self::EXPLORATION_LOST => self::EXPLORATION_LOST,
        self::ELECTROCUTED => self::ELECTROCUTED,
        self::INJURY => self::INJURY,
        self::BURNT => self::BURNT,
        self::CLUMSINESS => self::CLUMSINESS,
        self::SPACE_BATTLE => self::SPACE_BATTLE,
        self::SPACE_ASPHYXIATED => self::SPACE_ASPHYXIATED,
        self::BEHEADED => self::BEHEADED,
        self::STARVATION => self::STARVATION,
        self::QUARANTINE => self::QUARANTINE,
        self::BLACK_BITE => self::BLACK_BITE,
        self::METAL_PLATE => self::METAL_PLATE,
        self::ROCKETED => self::ROCKETED,
        self::BLED => self::BLED,
        self::INFECTION => self::INFECTION,
        self::MANKAROG => self::MANKAROG,
        self::PATROL_SHIP_EXPLOSION => self::PATROL_SHIP_EXPLOSION,
        self::NO_INFIRMERIE => self::NO_INFIRMERIE,
        ActionEnum::HIT => self::ASSASSINATED,
        ActionEnum::SHOOT => self::ASSASSINATED,
        ActionEnum::ATTACK => self::ASSASSINATED,
        PlayerEvent::METAL_PLATE => self::METAL_PLATE,
        StatusEnum::FIRE => self::BURNT,
        ActionEnum::REMOVE_SPORE => self::SELF_EXTRACTED,
        ModifierScopeEnum::EVENT_CLUMSINESS => self::CLUMSINESS,
        ActionEnum::AUTO_DESTROY => self::SUPER_NOVA,
        ActionEnum::COLLECT_SCRAP => self::SPACE_BATTLE,
        ActionEnum::LAND => self::PATROL_SHIP_EXPLOSION,
        ActionEnum::TAKEOFF => self::PATROL_SHIP_EXPLOSION,
        HunterEvent::HUNTER_SHOT => self::SPACE_BATTLE,
        ActionEnum::ADVANCE_DAEDALUS => self::ABANDONED,
        ActionEnum::LEAVE_ORBIT => self::ABANDONED,
        PlanetSectorEvent::FIGHT => self::EXPLORATION_COMBAT,
        PlanetSectorEvent::KILL_LOST => self::EXPLORATION_LOST,
        PlanetSectorEvent::PLANET_SECTOR_EVENT => self::EXPLORATION,
        ActionEnum::RETURN_TO_SOL => self::SOL_RETURN,
    ];

    public static function getAll(): array
    {
        return array_keys(self::DEATH_CAUSE_MAP);
    }

    public static function getHappyEndCauses(): ArrayCollection
    {
        return new ArrayCollection([
            self::SOL_RETURN,
            self::EDEN,
        ]);
    }
}
