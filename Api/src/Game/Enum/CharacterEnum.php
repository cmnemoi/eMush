<?php

namespace Mush\Game\Enum;

class CharacterEnum
{
    public const ANDIE = 'andie';
    public const DEREK = 'derek';
    public const ELEESHA = 'eleesha';
    public const FINOLA = 'finola';
    public const FRIEDA = 'frieda';
    public const GIOELE = 'gioele';
    public const IAN = 'ian';
    public const JANICE = 'janice';
    public const HUA = 'hua';
    public const KIM_JUN_SU = 'kim_jun_su';
    public const KUAN_TI = 'kuan_ti';
    public const PAOLA = 'paola';
    public const RALUCA = 'raluca';
    public const ROLAND = 'roland';
    public const STEPHEN = 'stephen';
    public const TERRENCE = 'terrence';
    public const CHAO = 'chao';
    public const CHUN = 'chun';

    public static function getAll()
    {
        return [
            self::ANDIE,
            self::DEREK,
            self::ELEESHA,
            self::FRIEDA,
            self::GIOELE,
            self::IAN,
            self::JANICE,
            self::HUA,
            self::KIM_JUN_SU,
            self::KUAN_TI,
            self::PAOLA,
            self::RALUCA,
            self::ROLAND,
            self::STEPHEN,
            self::TERRENCE,
            self::CHUN,
        ];
    }
}
