<?php

namespace Mush\Game\Enum;

use Mush\Triumph\Enum\TriumphScope;

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
    public const string NULL = '';
    public const string SOMEONE = 'someone';
    public const string MUSH = 'mush';

    public static array $characterColorMap = [
        self::ANDIE => '#DDDDDD',
        self::CHAO => '#5863B1',
        self::CHUN => '#DDD3CA',
        self::DEREK => '#FF4500',
        self::ELEESHA => '#D08500',
        self::FINOLA => '#4EA9B6',
        self::FRIEDA => '#206786',
        self::GIOELE => '#DD702A',
        self::HUA => '#805441',
        self::IAN => '#237C68',
        self::JANICE => '#B44753',
        self::JIN_SU => '#C0304C',
        self::KUAN_TI => '#F39B01',
        self::PAOLA => '#FF9AFA',
        self::RALUCA => '#868681',
        self::ROLAND => '#FFA36D',
        self::STEPHEN => '#BBBBBB',
        self::TERRENCE => '#D32837',
    ];

    public static function getAll(): array
    {
        return [
            self::ANDIE,
            self::CHAO,
            self::CHUN,
            self::DEREK,
            self::ELEESHA,
            self::FINOLA,
            self::FRIEDA,
            self::GIOELE,
            self::HUA,
            self::IAN,
            self::JANICE,
            self::JIN_SU,
            self::KUAN_TI,
            self::NERON,
            self::PAOLA,
            self::RALUCA,
            self::ROLAND,
            self::SOMEONE,
            self::STEPHEN,
            self::TERRENCE,
        ];
    }

    public static function getAllBiographies(): array
    {
        return [
            self::ANDIE,
            self::CHAO,
            self::CHUN,
            self::DEREK,
            self::ELEESHA,
            self::FINOLA,
            self::FRIEDA,
            self::GIOELE,
            self::HUA,
            self::IAN,
            self::JANICE,
            self::JIN_SU,
            self::KUAN_TI,
            self::PAOLA,
            self::RALUCA,
            self::ROLAND,
            self::STEPHEN,
            self::TERRENCE,
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
            self::SOMEONE,
            self::NERON,
        ], true);
    }

    public static function isFromRinaldoFamily(string $character): bool
    {
        return \in_array($character, [
            self::PAOLA,
            self::GIOELE,
        ], true);
    }

    public static function exists(string $character): bool
    {
        return \in_array($character, self::getAll(), true);
    }

    public static function doesNotExist(string $character): bool
    {
        return self::exists($character) === false;
    }

    public static function gender(string $character): string
    {
        return self::isMale($character) ? 'male' : 'female';
    }

    public static function chaolaPair(): array
    {
        return [self::CHAO, self::FINOLA];
    }

    public static function andrekPair(): array
    {
        return [self::ANDIE, self::DEREK];
    }

    public static function allPairs(): array
    {
        return array_merge(self::chaolaPair(), self::andrekPair());
    }

    public static function toPersonalTriumphScope(string $character): TriumphScope
    {
        return match ($character) {
            self::ANDIE => TriumphScope::PERSONAL_ANDIE,
            self::CHAO => TriumphScope::PERSONAL_CHAO,
            self::CHUN => TriumphScope::PERSONAL_CHUN,
            self::DEREK => TriumphScope::PERSONAL_DEREK,
            self::ELEESHA => TriumphScope::PERSONAL_ELEESHA,
            self::FINOLA => TriumphScope::PERSONAL_FINOLA,
            self::FRIEDA => TriumphScope::PERSONAL_FRIEDA,
            self::GIOELE => TriumphScope::PERSONAL_GIOELE,
            self::HUA => TriumphScope::PERSONAL_HUA,
            self::IAN => TriumphScope::PERSONAL_IAN,
            self::JANICE => TriumphScope::PERSONAL_JANICE,
            self::JIN_SU => TriumphScope::PERSONAL_JIN_SU,
            self::KUAN_TI => TriumphScope::PERSONAL_KUAN_TI,
            self::PAOLA => TriumphScope::PERSONAL_PAOLA,
            self::RALUCA => TriumphScope::PERSONAL_RALUCA,
            self::ROLAND => TriumphScope::PERSONAL_ROLAND,
            self::STEPHEN => TriumphScope::PERSONAL_STEPHEN,
            self::TERRENCE => TriumphScope::PERSONAL_TERRENCE,
            default => throw new \RuntimeException("No personal triumph scope for character {$character}"),
        };
    }
}
