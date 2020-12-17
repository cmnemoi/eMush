<?php

namespace Mush\Equipment\Enum;

class GameDrugEnum
{
    public const BACTA = 'bacta';
    public const BETAPROPYL = 'betapropyl';
    public const EUFURYLATE = 'eufurylate';
    public const NEWKE = 'newke';
    public const PHUXX = 'phuxx';
    public const PINQ = 'pinq';
    public const PYMP = 'pymp';
    public const ROSEBUD = 'rosebud';
    public const SOMA = 'soma';
    public const SPYCE = 'spyce';
    public const TWINOID = 'twinoid';
    public const XENOX = 'xenox';

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
}
