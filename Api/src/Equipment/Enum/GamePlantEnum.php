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
    public const FIBONICCUS = 'fiboniccus';
    public const MYCOPIA = 'mycopia';
    public const ASPERAGUNK = 'asperagunk';
    public const BUMPJUMPKIN = 'bumpjumpkin';

    /**
     * @return string[]
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
            self::BUMPJUMPKIN,
        ];
    }

    /**
     * @return string[]
     */
    public static function getAlienPlants(): array
    {
        return [
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
            self::BUMPJUMPKIN,
        ];
    }

    public static function getGameFruit(string $plantName): string
    {
        return array_flip(GameFruitEnum::getGamePlants())[$plantName];
    }
}
