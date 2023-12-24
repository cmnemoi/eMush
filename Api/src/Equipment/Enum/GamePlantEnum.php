<?php

namespace Mush\Equipment\Enum;

class GamePlantEnum
{
    public const BANANA_TREE = 'banana_tree';
    public const CREEPIST = 'creepist';
    public const CACTAX = 'cactax';
    public const BIFFLON = 'bifflon';
    public const PULMMINAGRO = 'pulminagro';
    public const PRECATUS = 'precatus';
    public const BUTTALIEN = 'buttalien';
    public const PLATACIA = 'platacia';
    public const TUBILISCUS = 'tubiliscus';
    public const GRAAPSHOOT = 'graapshoot';
    public const FIBONICCUS = 'Fiboniccus';
    public const MYCOPIA = 'mycopia';
    public const ASPERAGUNK = 'asperagunk';
    public const BUMPJUNKIN = 'bumpjunkin';

    /**
     * @return string[]
     *
     * @psalm-return array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string, 6: string, 7: string, 8: string, 9: string, 10: string, 11: string, 12: string, 13: string}
     */
    public static function getAll(): array
    {
        return [
            self::BANANA_TREE,
            self::CREEPIST,
            self::CACTAX,
            self::BIFFLON,
            self::PULMMINAGRO,
            self::PRECATUS,
            self::BUTTALIEN,
            self::PLATACIA,
            self::TUBILISCUS,
            self::GRAAPSHOOT,
            self::FIBONICCUS,
            self::MYCOPIA,
            self::ASPERAGUNK,
            self::BUMPJUNKIN,
        ];
    }

    public static function getGameFruit(string $plantName): string
    {
        return array_flip(GameFruitEnum::getGamePlants())[$plantName];
    }
}
