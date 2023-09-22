<?php

namespace Mush\Game\Enum;

class TriumphEnum
{
    public const ALIEN_SCIENCE = 'alien_science';
    public const EXPEDITION = 'expedition';
    public const SUPER_NOVA = 'super_nova';
    public const FIRST_STARMAP = 'first_starmap';
    public const NEXT_STARMAP = 'next_starmap';
    public const PILGRED = 'pilgred';
    public const STARTING_MUSH = 'starting_mush';
    public const CYCLE_MUSH = 'cycle_mush';
    public const CYCLE_MUSH_LATE = 'cycle_mush_late';
    public const CONVERSION = 'conversion';
    public const INFECTION = 'infection';
    public const HUMANOCIDE = 'humanocide';
    public const CHUN_DEAD = 'chun_dead';
    public const SOL_RETURN_MUSH = 'sol_return_mush';
    public const EDEN_MUSH = 'eden_mush';
    public const CYCLE_HUMAN = 'cycle_human';
    public const CYCLE_INACTIVE = 'cycle_inactive';
    public const NEW_PLANET_ORBIT = 'new_planet_orbit';
    public const SOL_CONTACT = 'sol_contact';
    public const SMALL_RESEARCH = 'small_research';
    public const STANDARD_RESEARCH = 'standard_research';
    public const BRILLIANT_RESEARCH = 'brilliant_research';
    public const SOL_RETURN = 'sol_return';
    public const SOL_MUSH_INTRUDER = 'sol_mush_intruder';
    public const HUNTER_KILLED = 'hunter_killed';
    public const MUSHICIDE = 'mushicide';
    public const REBEL_WOLF = 'rebel_wolf';
    public const NICE_SURGERY = 'nice_surgery';
    public const EDEN_CREW_ALIVE = 'eden_crew_alive';
    public const EDEN_ALIEN_PLANT = 'eden_alien_plant';
    public const EDEN_GENDER = 'eden_gender';
    public const EDEN = 'eden';
    public const EDEN_CAT = 'eden_cat';
    public const EDEN_CAT_DEAD = 'eden_cat_dead';
    public const EDEN_CAT_MUSH = 'eden_cat_mush';
    public const EDEN_DISEASE = 'eden_disease';
    public const EDEN_ENGINEERS = 'eden_engineers';
    public const EDEN_BIOLOGIST = 'eden_biologist';
    public const EDEN_MUSH_INTRUDER = 'eden_mush_intruder';
    public const EDEN_BY_PREGNANT = 'eden_by_pregnant';
    public const EDEN_COMPUTED = 'eden_computed';
    public const ANATHEMA = 'anathema';
    public const PREGNANCY = 'pregnancy';
    public const ALL_PREGNANT = 'all_pregnant';
    public const SUCCESS_REPAIR = 'success_repair';
    public const SUCCESS_REPAIR_HULL = 'success_repair_hull';
    public const SUCCESS_FIRE = 'success_fire';
    public const MUSH_VACCINATED = 'mush_vaccinated';
    public const ALIEN_KILLED = 'alien_killed';

    /**
     * @return string[]
     */
    public static function getMushTriumph(): array
    {
        return [
            self::CYCLE_MUSH,
            self::STARTING_MUSH,
            self::CYCLE_MUSH_LATE,
            self::CONVERSION,
            self::INFECTION,
            self::HUMANOCIDE,
            self::CHUN_DEAD,
            self::SOL_RETURN_MUSH,
            self::EDEN_MUSH,
        ];
    }

    /**
     * @return string[]
     */
    public static function getHumanTriumph(): array
    {
        return [
            self::CYCLE_HUMAN,
            self::CYCLE_INACTIVE,
            self::NEW_PLANET_ORBIT,
            self::SOL_CONTACT,
            self::SMALL_RESEARCH,
            self::STANDARD_RESEARCH,
            self::BRILLIANT_RESEARCH,
            self::SOL_RETURN,
            self::SOL_MUSH_INTRUDER,
            self::HUNTER_KILLED,
            self::MUSHICIDE,
            self::REBEL_WOLF,
            self::NICE_SURGERY,
            self::EDEN_CREW_ALIVE,
            self::EDEN_ALIEN_PLANT,
            self::EDEN_GENDER,
            self::EDEN,
            self::EDEN_CAT,
            self::EDEN_CAT_DEAD,
            self::EDEN_CAT_MUSH,
            self::EDEN_DISEASE,
            self::EDEN_ENGINEERS,
            self::EDEN_BIOLOGIST,
            self::EDEN_MUSH_INTRUDER,
            self::EDEN_BY_PREGNANT,
            self::EDEN_COMPUTED,
            self::ANATHEMA,
            self::PREGNANCY,
            self::ALL_PREGNANT,
            self::SUCCESS_REPAIR,
            self::SUCCESS_REPAIR_HULL,
            self::SUCCESS_FIRE,
            self::MUSH_VACCINATED,
            self::ALIEN_KILLED,
        ];
    }

    /**
     * @return string[]
     */
    public static function getReachingEden(): array
    {
        return [
            self::EDEN_CREW_ALIVE,
            self::EDEN_ALIEN_PLANT,
            self::EDEN_GENDER,
            self::EDEN,
            self::EDEN_CAT,
            self::EDEN_CAT_DEAD,
            self::EDEN_CAT_MUSH,
            self::EDEN_DISEASE,
            self::EDEN_ENGINEERS,
            self::EDEN_BIOLOGIST,
            self::EDEN_MUSH_INTRUDER,
            self::EDEN_BY_PREGNANT,
            self::EDEN_COMPUTED,
            self::EDEN_MUSH,
        ];
    }
}
