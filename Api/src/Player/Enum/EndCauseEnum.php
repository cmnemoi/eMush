<?php

namespace Mush\Player\Enum;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Hunter\Event\HunterEvent;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;

abstract class EndCauseEnum
{
    public const string STILL_LIVING = 'still_living';
    // admin only
    public const string SUICIDE = 'suicide';
    public const string SOL_RETURN = 'sol_return';
    public const string EDEN = 'eden';
    public const string DAEDALUS_DESTROYED = 'daedalus_destroyed';
    public const string KILLED_BY_NERON = 'killed_by_neron';
    public const string SUPER_NOVA = 'super_nova';
    public const string ALIEN_ABDUCTED = 'alien_abducted';
    public const string ASSASSINATED = 'assassinated';
    public const string DEPRESSION = 'depression';
    public const string ASPHYXIA = 'asphyxia';
    public const string ABANDONED = 'abandoned';
    public const string ALLERGY = 'allergy';
    public const string SELF_EXTRACTED = 'self_extracted';
    public const string EXPLORATION = 'exploration';
    public const string EXPLORATION_COMBAT = 'exploration_combat';
    public const string EXPLORATION_LOST = 'exploration_lost';
    public const string ELECTROCUTED = 'electrocuted';
    public const string INJURY = 'injury';
    public const string BURNT = 'burnt';
    public const string CLUMSINESS = 'clumsiness';
    public const string SPACE_BATTLE = 'space_battle';
    public const string SPACE_ASPHYXIATED = 'space_asphyxiated';
    public const string BEHEADED = 'beheaded';
    public const string STARVATION = 'starvation';
    public const string QUARANTINE = 'quarantine';
    public const string BLACK_BITE = 'black_bite';
    public const string METAL_PLATE = 'metal_plate';
    public const string ROCKETED = 'rocketed';
    public const string BLED = 'bled';
    public const string INFECTION = 'infection';
    public const string MANKAROG = 'mankarog';
    public const string PATROL_SHIP_EXPLOSION = 'patrol_ship_explosion';
    public const string NO_INFIRMERIE = 'no_infirmerie'; // cause of death lost in a bug

    public const array DEATH_CAUSE_MAP = [
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
        ActionEnum::HIT->value => self::ASSASSINATED,
        ActionEnum::SHOOT->value => self::ASSASSINATED,
        ActionEnum::ATTACK->value => self::ASSASSINATED,
        PlayerEvent::METAL_PLATE => self::METAL_PLATE,
        StatusEnum::FIRE => self::BURNT,
        ActionEnum::REMOVE_SPORE->value => self::SELF_EXTRACTED,
        ModifierScopeEnum::EVENT_CLUMSINESS => self::CLUMSINESS,
        ActionEnum::AUTO_DESTROY->value => self::SUPER_NOVA,
        ActionEnum::COLLECT_SCRAP->value => self::SPACE_BATTLE,
        ActionEnum::LAND->value => self::PATROL_SHIP_EXPLOSION,
        ActionEnum::TAKEOFF->value => self::PATROL_SHIP_EXPLOSION,
        HunterEvent::HUNTER_SHOT => self::SPACE_BATTLE,
        ActionEnum::ADVANCE_DAEDALUS->value => self::ABANDONED,
        ActionEnum::LEAVE_ORBIT->value => self::ABANDONED,
        PlanetSectorEvent::FIGHT => self::EXPLORATION_COMBAT,
        PlanetSectorEvent::KILL_LOST => self::EXPLORATION_LOST,
        PlanetSectorEvent::PLANET_SECTOR_EVENT => self::EXPLORATION,
        ActionEnum::RETURN_TO_SOL->value => self::SOL_RETURN,
        PlayerStatusEnum::STARVING => self::STARVATION,
        ActionEnum::TRAVEL_TO_EDEN->value => self::EDEN,
    ];

    public static function getAll(): array
    {
        return array_keys(self::DEATH_CAUSE_MAP);
    }

    public static function getNotDeathEndCauses(): ArrayCollection
    {
        return new ArrayCollection([
            self::SOL_RETURN,
            self::EDEN,
        ]);
    }

    public static function isDeathEndCause(string $endCause): bool
    {
        return self::getDeathEndCauses()->contains($endCause);
    }

    public static function isNotDeathEndCause(string $endCause): bool
    {
        return self::getNotDeathEndCauses()->contains($endCause);
    }

    public static function doesNotRemoveMorale(string $endCause): bool
    {
        return self::getNotDeathEndCauses()->contains($endCause) === true || self::getEndCausesWhichDoNotRemoveMorale()->contains($endCause) === true;
    }

    public static function mapEndCause(array $tags): string
    {
        $logs = array_intersect_key(self::DEATH_CAUSE_MAP, array_flip($tags));

        if (\count($logs) > 0) {
            return reset($logs);
        }

        throw new \LogicException('Cannot find a matching end cause');
    }

    private static function getEndCausesWhichDoNotRemoveMorale(): ArrayCollection
    {
        return new ArrayCollection([
            self::SUICIDE,
            self::DAEDALUS_DESTROYED,
            self::KILLED_BY_NERON,
            self::SUPER_NOVA,
            self::DEPRESSION,
            self::QUARANTINE,
        ]);
    }

    private static function getDeathEndCauses(): ArrayCollection
    {
        return new ArrayCollection([
            self::ABANDONED,
            self::ALIEN_ABDUCTED,
            self::ALLERGY,
            self::ASPHYXIA,
            self::ASSASSINATED,
            self::BEHEADED,
            self::BLACK_BITE,
            self::BLED,
            self::BURNT,
            self::CLUMSINESS,
            self::DAEDALUS_DESTROYED,
            self::DEPRESSION,
            self::ELECTROCUTED,
            self::EXPLORATION_COMBAT,
            self::EXPLORATION_LOST,
            self::EXPLORATION,
            self::INFECTION,
            self::INJURY,
            self::KILLED_BY_NERON,
            self::MANKAROG,
            self::METAL_PLATE,
            self::NO_INFIRMERIE,
            self::PATROL_SHIP_EXPLOSION,
            self::QUARANTINE,
            self::ROCKETED,
            self::SELF_EXTRACTED,
            self::SPACE_ASPHYXIATED,
            self::SPACE_BATTLE,
            self::STARVATION,
            self::SUICIDE,
            self::SUPER_NOVA,
        ]);
    }
}
