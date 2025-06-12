<?php

namespace Mush\Daedalus\Enum;

abstract class FunFactEnum
{
    public const string BEST_COOK = 'best_cook';
    public const string BEST_PLANET_SCANNER = 'best_planet_scanner';
    public const string EARLIEST_DEATH = 'earliest_death';
    public const string BEST_TECHNICIAN = 'best_technician';
    public const string UNLUCKIER_TECHNICIAN = 'unluckier_technician';
    public const string SOL_COLLABS = 'sol_collabs';
    public const string BEST_CARESSER = 'best_caresser';
    public const string BEST_HUNTER_KILLER = 'best_hunter_killer';
    public const string BEST_LOST = 'best_lost';
    public const string BEST_KILLER = 'best_killer';
    public const string MOST_TALKATIVE = 'most_talkative';
    public const string LESS_TALKATIVE = 'less_talkative';
    public const string LESS_ACTIVE = 'less_active';
    public const string MOST_ACTIVE = 'most_active';
    public const string BEST_EATER = 'best_eater';
    public const string BEST_ACTION_WASTER = 'best_action_waster';
    public const string WORST_ACTION_WASTER = 'worst_action_waster';
    public const string BEST_SLEEPER = 'best_sleeper';
    public const string DEAD_DURING_SLEEP = 'dead_during_sleep';
    public const string BEST_HACKER = 'best_hacker';
    public const string BEST_COM_TECHNICIAN = 'best_com_technician';
    public const string BEST_SANDMAN = 'best_sandman';
    public const string BEST_TERRORIST = 'best_terrorist';
    public const string BEST_WOUNDED = 'best_wounded';
    public const string BEST_DISEASED = 'best_diseased';
    public const string DRUG_ADDICT = 'drug_addict';
    public const string KNIFE_EVADER = 'knife_evader';
    public const string BEST_AGRO = 'best_agro';
    public const string WORST_AGRO = 'worst_agro';
    public const string LESSER_DRUGGED = 'lesser_drugged';
    public const string KUBE_ADDICT = 'kube_addict';
    public const string BEST_ALIEN_TRAITOR = 'best_alien_traitor';
    public const string STEALTHIEST = 'stealthiest';
    public const string UNSTEALTHIEST = 'unstealthiest';
    public const string UNSTEALTHIEST_AND_KILLED = 'unstealthiest_and_killed';

    public static function getAll(): array
    {
        return [
            self::BEST_COOK,
            self::BEST_PLANET_SCANNER,
            self::EARLIEST_DEATH,
            self::BEST_TECHNICIAN,
            self::UNLUCKIER_TECHNICIAN,
            self::SOL_COLLABS,
            self::BEST_CARESSER,
            self::BEST_HUNTER_KILLER,
            self::BEST_LOST,
            self::BEST_KILLER,
            self::MOST_TALKATIVE,
            self::LESS_TALKATIVE,
            self::LESS_ACTIVE,
            self::MOST_ACTIVE,
            self::BEST_EATER,
            self::BEST_ACTION_WASTER,
            self::WORST_ACTION_WASTER,
            self::BEST_SLEEPER,
            self::DEAD_DURING_SLEEP,
            self::BEST_HACKER,
            self::BEST_COM_TECHNICIAN,
            self::BEST_SANDMAN,
            self::BEST_TERRORIST,
            self::BEST_WOUNDED,
            self::BEST_DISEASED,
            self::DRUG_ADDICT,
            self::KNIFE_EVADER,
            self::BEST_AGRO,
            self::WORST_AGRO,
            self::LESSER_DRUGGED,
            self::KUBE_ADDICT,
            self::BEST_ALIEN_TRAITOR,
            self::STEALTHIEST,
            self::UNSTEALTHIEST,
            self::UNSTEALTHIEST_AND_KILLED,
        ];
    }

    public static function looksForGreatestStatValue(string $funFact): bool
    {
        return \in_array($funFact, [
            self::BEST_COOK,
            self::BEST_PLANET_SCANNER,
            self::BEST_TECHNICIAN,
            self::SOL_COLLABS,
            self::BEST_CARESSER,
            self::BEST_HUNTER_KILLER,
            self::BEST_LOST,
            self::BEST_KILLER,
            self::MOST_TALKATIVE,
            self::MOST_ACTIVE,
            self::BEST_EATER,
            self::BEST_ACTION_WASTER,
            self::BEST_SLEEPER,
            self::BEST_HACKER,
            self::BEST_COM_TECHNICIAN,
            self::BEST_SANDMAN,
            self::BEST_TERRORIST,
            self::BEST_WOUNDED,
            self::BEST_DISEASED,
            self::DRUG_ADDICT,
            self::KNIFE_EVADER,
            self::BEST_AGRO,
            self::KUBE_ADDICT,
            self::BEST_ALIEN_TRAITOR,
            self::STEALTHIEST,
        ], true);
    }

    public static function looksForSmallestStatValue(string $funFact): bool
    {
        return \in_array($funFact, [
            self::LESS_TALKATIVE,
            self::LESS_ACTIVE,
            self::WORST_ACTION_WASTER,
            self::WORST_AGRO,
            self::LESSER_DRUGGED,
        ], true);
    }
}
