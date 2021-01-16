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
    public const KIM_JIN_SU = 'kim_jin_su';
    public const KUAN_TI = 'kuan_ti';
    public const PAOLA = 'paola';
    public const RALUCA = 'raluca';
    public const ROLAND = 'roland';
    public const STEPHEN = 'stephen';
    public const TERRENCE = 'terrence';
    public const CHAO = 'chao';
    public const CHUN = 'chun';

    /**
     * @return string[]
     *
     * @psalm-return array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string, 6: string, 7: string, 8: string, 9: string, 10: string, 11: string, 12: string, 13: string, 14: string, 15: string}
     */
    public static function getAll(): array
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
            self::KIM_JIN_SU,
            self::KUAN_TI,
            self::PAOLA,
            self::RALUCA,
            self::ROLAND,
            self::STEPHEN,
            self::TERRENCE,
            self::CHUN,
        ];
    }

    public static function isMale(string $character): bool
    {
        return in_array($character, [
            self::ANDIE,
            self::TERRENCE,
            self::DEREK,
            self::GIOELE,
            self::IAN,
            self::KIM_JIN_SU,
            self::KUAN_TI,
            self::ROLAND,
            self::STEPHEN,
        ]);
    }
}
