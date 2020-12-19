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

    /**
     * @return string[]
     *
     * @psalm-return array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string, 6: string, 7: string, 8: string, 9: string, 10: string, 11: string}
     */
    public static function getAll(): array
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
