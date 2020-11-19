<?php

namespace Mush\Item\Enum;

class GameFruitEnum
{
    public const BANANA = 'banana';
    public const CREEPNUT = 'creepnut';
    public const MEZTINE = 'meztine';
    public const GUNTIFLOP = 'guntiflop';
    public const PLOSHMINA = 'ploshmina';
    public const PRECATI = 'precati';
    public const BOTTINE = 'bottine';
    public const FRAGILANE = 'fragilane';
    public const ANEMOLE = 'anemole';
    public const PENICRAFT = 'peniraft';
    public const KUBINUS = 'kubinus';
    public const CALEBOOT = 'caleboot';
    public const FILANDRA = 'filandra';
    public const JUNKIN = 'junkin';

    public static function getAll()
    {
        return [
            self::BANANA,
            self::CREEPNUT,
            self::MEZTINE,
            self::GUNTIFLOP,
            self::PLOSHMINA,
            self::PRECATI,
            self::BOTTINE,
            self::FRAGILANE,
            self::ANEMOLE,
            self::PENICRAFT,
            self::KUBINUS,
            self::CALEBOOT,
            self::FILANDRA,
            self::JUNKIN,
        ];
    }

    public static function getGamePlant(string $fruitName): string
    {
        return self::getGamePlants()[$fruitName];
    }

    public static function getGamePlants()
    {
        return [
            self::BANANA => GamePlantEnum::BANANA_TREE,
            self::CREEPNUT => GamePlantEnum::CREEPIST,
            self::MEZTINE => GamePlantEnum::CACTAX,
            self::GUNTIFLOP => GamePlantEnum::BIFFLON,
            self::PLOSHMINA => GamePlantEnum::PULMMINAGRO,
            self::PRECATI => GamePlantEnum::PRECATUS,
            self::BOTTINE => GamePlantEnum::BUTTALIEN,
            self::FRAGILANE => GamePlantEnum::PLATACIA,
            self::ANEMOLE => GamePlantEnum::TUBILISCUS,
            self::PENICRAFT => GamePlantEnum::GRAAPSHOOT,
            self::KUBINUS => GamePlantEnum::FIBONICCUS,
            self::CALEBOOT => GamePlantEnum::MYCOPIA,
            self::FILANDRA => GamePlantEnum::ASPERAGUNK,
        ];
    }
}
