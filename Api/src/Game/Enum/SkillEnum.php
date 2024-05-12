<?php

namespace Mush\Game\Enum;

use Doctrine\Common\Collections\ArrayCollection;

abstract class SkillEnum
{
    public const string ANTIQUE_PERFUME = 'antique_perfume';
    public const string APPRENTICE = 'apprentice';
    public const string ASTROPHYSICIST = 'astrophysicist';
    public const string BIOLOGIST = 'biologist';
    public const string BOTANIST = 'botanist';
    public const string CAFFEINE_JUNKIE = 'caffeine_junkie';
    public const string CHEF = ' chef';
    public const string COLD_BLOODED = 'cold_blooded';
    public const string CONFIDENT = 'confident';
    public const string CRAZY_EYE = 'crazy_eye';
    public const string CREATIVE = 'creative';
    public const string CONCEPTOR = 'conceptor';
    public const string DETACHED_CREWMEMBER = 'detached_crewmember';
    public const string DEVOTION = 'devotion';
    public const string DETERMINED = 'determined';
    public const string DIPLOMAT = 'diplomat';
    public const string EXPERT = 'expert';
    public const string FIREFIGHTER = 'firefighter';
    public const string FRUGIVORE = 'frugivore';
    public const string GENIUS = 'genius';
    public const string GREEN_THUMB = 'green_thumb';
    public const string GUNNER = 'gunner';
    public const string INTIMIDATING = 'intimidating';
    public const string IT_EXPERT = 'it_expert';
    public const string LEADER = 'leader';
    public const string LETHARGY = 'lethargy';
    public const string LOGISTICS_EXPERT = 'logistics_expert';
    public const string MANKIND_ONLY_HOPE = 'mankind_only_hope';
    public const string MEDIC = 'medic';
    public const string METALWORKER = 'metalworker';
    public const string MOTIVATOR = 'motivator';
    public const string MYCOLOGIST = 'mycologist';
    public const string NERON_ONLY_FRIEND = 'neron_only_friend';
    public const string NURSE = 'nurse';
    public const string OBSERVANT = 'observant';
    public const string OCD = 'ocd';
    public const string OPPORTUNIST = 'opportunist';
    public const string PANIC = 'panic';
    public const string PARANOID = 'paranoid';
    public const string PHYSICIST = 'physicist';
    public const string PILOT = 'pilot';
    public const string POLITICIAN = 'politician';
    public const string POLYMATH = 'polymath';
    public const string POLYVALENT = 'polyvalent';
    public const string PREMONITION = 'premonition';
    public const string RADIO_EXPERT = 'radio_expert';
    public const string REBEL = 'rebel';
    public const string ROBOTICS_EXPERT = 'robotics_expert';
    public const string SELF_SACRIFICE = 'self_sacrifice';
    public const string SHOOTER = 'shooter';
    public const string SHRINK = 'shrink';
    public const string SNEAK = 'sneak';
    public const string SOLID = 'solid';
    public const string SPRINTER = 'sprinter';
    public const string STRATEGURU = 'strateguru';
    public const string SURVIVALIST = 'survivalist';
    public const string TECHNICIAN = 'technician';
    public const string OPTIMIST = 'optimist';
    public const string TORTURER = 'torturer';
    public const string TRACKER = 'tracker';
    public const string U_TURN = 'u_turn';
    public const string VICTIMIZER = 'victimizer';
    public const string WRESTLER = 'wrestler';
    public const string HYGIENIST = 'hygienist';

    public static function getAll(): ArrayCollection
    {
        return new ArrayCollection([
            self::ANTIQUE_PERFUME,
            self::APPRENTICE,
            self::ASTROPHYSICIST,
            self::BIOLOGIST,
            self::BOTANIST,
            self::CAFFEINE_JUNKIE,
            self::CHEF,
            self::COLD_BLOODED,
            self::CONFIDENT,
            self::CRAZY_EYE,
            self::CREATIVE,
            self::CONCEPTOR,
            self::DETACHED_CREWMEMBER,
            self::DEVOTION,
            self::DETERMINED,
            self::DIPLOMAT,
            self::EXPERT,
            self::FIREFIGHTER,
            self::FRUGIVORE,
            self::GENIUS,
            self::GREEN_THUMB,
            self::GUNNER,
            self::INTIMIDATING,
            self::IT_EXPERT,
            self::LEADER,
            self::LETHARGY,
            self::LOGISTICS_EXPERT,
            self::MANKIND_ONLY_HOPE,
            self::MEDIC,
            self::METALWORKER,
            self::MOTIVATOR,
            self::MYCOLOGIST,
            self::NERON_ONLY_FRIEND,
            self::NURSE,
            self::OBSERVANT,
            self::OCD,
            self::OPPORTUNIST,
            self::PANIC,
            self::PARANOID,
            self::PHYSICIST,
            self::PILOT,
            self::POLITICIAN,
            self::POLYMATH,
            self::POLYVALENT,
            self::PREMONITION,
            self::RADIO_EXPERT,
            self::REBEL,
            self::ROBOTICS_EXPERT,
            self::SELF_SACRIFICE,
            self::SHOOTER,
            self::SHRINK,
            self::SNEAK,
            self::SOLID,
            self::SPRINTER,
            self::STRATEGURU,
            self::SURVIVALIST,
            self::TECHNICIAN,
            self::OPTIMIST,
            self::TORTURER,
            self::TRACKER,
            self::U_TURN,
            self::VICTIMIZER,
            self::WRESTLER,
            self::HYGIENIST,
        ]);
    }

    public static function isSkill(string $skill): bool
    {
        return self::getAll()->contains($skill);
    }
}
