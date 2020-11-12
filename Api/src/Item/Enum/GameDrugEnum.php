<?php

namespace Mush\Item\Enum;

class GameDrugEnum
{
    public const BACTA = 'Bacta';
    public const BETAPROPYL = 'Betapropyl';
    public const EUFURYLATE = 'Eufurylate';
    public const NEWKE = 'Newke';
    public const PHUXX = 'Phuxx';
    public const PINQ = 'Pinq';
    public const PYMP = 'Pymp';
    public const ROSEBUD = 'Rosebud';
    public const SOMA = 'Soma';
    public const SPYCE = 'Spyce';
    public const TWINOID = 'Twinoid';
    public const XENOX = 'Xenox';

    public static function getAll()
    {
        return [
            self::BACTA,
            self::BETAPROPYL,
            self::EUFURYLATE,
            self::NEWKE,
            self::PHUXX,
            self::PINQ,
            self::PYMP,
            self::ROSEBUD,
            self::SOMA,
            self::SPYCE,
            self::TWINOID,
            self::XENOX,
        ];
    }

    public static function getGameDrug(string $drugName): string
    {
        return array_flip(GameDrugEnum::getGameDrug())[$drugName];
    }
}
