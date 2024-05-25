<?php

namespace Mush\Game\Enum;

abstract class CharacterEnum
{
    public const string ANDIE = 'andie';
    public const string DEREK = 'derek';
    public const string ELEESHA = 'eleesha';
    public const string FINOLA = 'finola';
    public const string FRIEDA = 'frieda';
    public const string GIOELE = 'gioele';
    public const string IAN = 'ian';
    public const string JANICE = 'janice';
    public const string HUA = 'hua';
    public const string JIN_SU = 'jin_su';
    public const string KUAN_TI = 'kuan_ti';
    public const string PAOLA = 'paola';
    public const string RALUCA = 'raluca';
    public const string ROLAND = 'roland';
    public const string STEPHEN = 'stephen';
    public const string TERRENCE = 'terrence';
    public const string CHAO = 'chao';
    public const string CHUN = 'chun';
    public const string NERON = 'neron';
    public const string null = '';

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
            self::JIN_SU,
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
        return \in_array($character, [
            self::ANDIE,
            self::TERRENCE,
            self::DEREK,
            self::CHAO,
            self::GIOELE,
            self::IAN,
            self::JIN_SU,
            self::KUAN_TI,
            self::ROLAND,
            self::STEPHEN,
        ], true);
    }

    public static function isFromRinaldoFamily(string $character): bool
    {
        return \in_array($character, [
            self::PAOLA,
            self::GIOELE,
        ], true);
    }
}
