<?php

namespace Mush\Game\Enum;

abstract class TriumphEnum
{
    public const string ALIEN_SCIENCE = 'alien_science';
    public const string EXPEDITION = 'expedition';
    public const string SUPER_NOVA = 'super_nova';
    public const string FIRST_STARMAP = 'first_starmap';
    public const string NEXT_STARMAP = 'next_starmap';
    public const string PILGRED = 'pilgred';
    public const string STARTING_MUSH = 'starting_mush';
    public const string CYCLE_MUSH = 'cycle_mush';
    public const string CYCLE_MUSH_LATE = 'cycle_mush_late';
    public const string CONVERSION = 'conversion';
    public const string INFECTION = 'infection';
    public const string HUMANOCIDE = 'humanocide';
    public const string CHUN_DEAD = 'chun_dead';
    public const string SOL_RETURN_MUSH = 'sol_return_mush';
    public const string EDEN_MUSH = 'eden_mush';
    public const string CYCLE_HUMAN = 'cycle_human';
    public const string CYCLE_INACTIVE = 'cycle_inactive';
    public const string NEW_PLANET_ORBIT = 'new_planet_orbit';
    public const string SOL_CONTACT = 'sol_contact';
    public const string SMALL_RESEARCH = 'small_research';
    public const string STANDARD_RESEARCH = 'standard_research';
    public const string BRILLIANT_RESEARCH = 'brilliant_research';
    public const string SOL_RETURN = 'sol_return';
    public const string SOL_MUSH_INTRUDER = 'sol_mush_intruder';
    public const string HUNTER_KILLED = 'hunter_killed';
    public const string MUSHICIDE = 'mushicide';
    public const string REBEL_WOLF = 'rebel_wolf';
    public const string NICE_SURGERY = 'nice_surgery';
    public const string EDEN_CREW_ALIVE = 'eden_crew_alive';
    public const string EDEN_ALIEN_PLANT = 'eden_alien_plant';
    public const string EDEN_GENDER = 'eden_gender';
    public const string EDEN = 'eden';
    public const string EDEN_CAT = 'eden_cat';
    public const string EDEN_CAT_DEAD = 'eden_cat_dead';
    public const string EDEN_CAT_MUSH = 'eden_cat_mush';
    public const string EDEN_DISEASE = 'eden_disease';
    public const string EDEN_ENGINEERS = 'eden_engineers';
    public const string EDEN_BIOLOGIST = 'eden_biologist';
    public const string EDEN_MUSH_INTRUDER = 'eden_mush_intruder';
    public const string EDEN_BY_PREGNANT = 'eden_by_pregnant';
    public const string EDEN_COMPUTED = 'eden_computed';
    public const string ANATHEMA = 'anathema';
    public const string PREGNANCY = 'pregnancy';
    public const string ALL_PREGNANT = 'all_pregnant';
    public const string SUCCESS_REPAIR = 'success_repair';
    public const string SUCCESS_REPAIR_HULL = 'success_repair_hull';
    public const string SUCCESS_FIRE = 'success_fire';
    public const string MUSH_VACCINATED = 'mush_vaccinated';
    public const string ALIEN_KILLED = 'alien_killed';

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
