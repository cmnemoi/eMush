<?php

namespace Mush\Equipment\Enum;

use Doctrine\Common\Collections\ArrayCollection;

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

    /**
     * @return string[]
     *
     * @psalm-return array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string, 6: string, 7: string, 8: string, 9: string, 10: string, 11: string, 12: string, 13: string}
     */
    public static function getAll(): array
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

    /**
     * @return string[]
     *
     * @psalm-return array{banana: string, creepnut: string, meztine: string, guntiflop: string, ploshmina: string, precati: string, bottine: string, fragilane: string, anemole: string, peniraft: string, kubinus: string, caleboot: string, filandra: string}
     */
    public static function getGamePlants(): array
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

    public static function getAlienFruits(): ArrayCollection
    {
        return new ArrayCollection([
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
        ]);
    }
}
