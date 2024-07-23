<?php

namespace Mush\Skill\Enum;

use Doctrine\Common\Collections\ArrayCollection;

enum SkillName: string
{
    case ANONYMUSH = 'anonymush';
    case ANTIQUE_PERFUME = 'antique_perfume';
    case APPRENTICE = 'apprentice';
    case ASTROPHYSICIST = 'astrophysicist';
    case BACTEROPHILIAC = 'bacterophiliac';
    case BIOLOGIST = 'biologist';
    case BOTANIST = 'botanist';
    case BYPASS = 'bypass';
    case CAFFEINE_JUNKIE = 'caffeine_junkie';
    case CHEF = 'chef';
    case COLD_BLOODED = 'cold_blooded';
    case CONCEPTOR = 'conceptor';
    case CONFIDENT = 'confident';
    case CRAZY_EYE = 'crazy_eye';
    case CREATIVE = 'creative';
    case DEFACER = 'defacer';
    case DEMORALIZE = 'demoralize';
    case DETACHED_CREWMEMBER = 'detached_crewmember';
    case DETERMINED = 'determined';
    case DEVOTION = 'devotion';
    case DIPLOMAT = 'diplomat';
    case DOORMAN = 'doorman';
    case EXPERT = 'expert';
    case FERTILE = 'fertile';
    case FIREFIGHTER = 'firefighter';
    case FRUGIVORE = 'frugivore';
    case FUNGAL_KITCHEN = 'fungal_kitchen';
    case GENIUS = 'genius';
    case GREEN_JELLY = 'green_jelly';
    case GREEN_THUMB = 'green_thumb';
    case GUNNER = 'gunner';
    case HARD_BOILED = 'hard_boiled';
    case HYGIENIST = 'hygienist';
    case INFECTOR = 'infector';
    case INTIMIDATING = 'intimidating';
    case IT_EXPERT = 'it_expert';
    case LEADER = 'leader';
    case LETHARGY = 'lethargy';
    case LOGISTICS_EXPERT = 'logistics_expert';
    case MANKIND_ONLY_HOPE = 'mankind_only_hope';
    case MASSIVE_MUSHIFICATION = 'massive_mushification';
    case MEDIC = 'medic';
    case METALWORKER = 'metalworker';
    case MOTIVATOR = 'motivator';
    case MYCELIUM_SPIRIT = 'mycelium_spirit';
    case MYCOLOGIST = 'mycologist';
    case NERON_DEPRESSION = 'neron_depression';
    case NERON_ONLY_FRIEND = 'neron_only_friend';
    case NIGHTMARISH = 'nightmarish';
    case NIMBLE_FINGERS = 'nimble_fingers';
    case NINJA = 'ninja';
    case NULL = '';
    case NURSE = 'nurse';
    case OBSERVANT = 'observant';
    case OCD = 'ocd';
    case OPPORTUNIST = 'opportunist';
    case OPTIMIST = 'optimist';
    case PANIC = 'panic';
    case PARANOID = 'paranoid';
    case PHAGOCYTE = 'phagocyte';
    case PHYSICIST = 'physicist';
    case PILOT = 'pilot';
    case POLITICIAN = 'politician';
    case POLYMATH = 'polymath';
    case POLYVALENT = 'polyvalent';
    case PREMONITION = 'premonition';
    case PYROMANIAC = 'pyromaniac';
    case RADIO_EXPERT = 'radio_expert';
    case RADIO_PIRACY = 'radio_piracy';
    case REBEL = 'rebel';
    case ROBOTICS_EXPERT = 'robotics_expert';
    case SABOTEUR = 'saboteur';
    case SELF_SACRIFICE = 'self_sacrifice';
    case SHOOTER = 'shooter';
    case SHRINK = 'shrink';
    case SLIMETRAP = 'slimetrap';
    case SNEAK = 'sneak';
    case SOLID = 'solid';
    case SPLASHPROOF = 'splashproof';
    case SPRINTER = 'sprinter';
    case STRATEGURU = 'strateguru';
    case SURVIVALIST = 'survivalist';
    case TECHNICIAN = 'technician';
    case TORTURER = 'torturer';
    case TRACKER = 'tracker';
    case TRAITOR = 'traitor';
    case TRANSFER = 'transfer';
    case TRAPPER = 'trapper';
    case U_TURN = 'u_turn';
    case VICTIMIZER = 'victimizer';
    case WRESTLER = 'wrestler';

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
