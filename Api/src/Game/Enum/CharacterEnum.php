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

    public static array $characterColorMap = [
        self::ANDIE => '#DDDDDD',
        self::CHAO => '#5863B1',
        self::CHUN => '#DDD3CA',
        self::DEREK => '#FF4500',
        self::ELEESHA => '#D08500',
        self::FINOLA => '#4EA9B6',
        self::FRIEDA => '#206786',
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
