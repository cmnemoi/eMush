<?php

namespace Mush\Equipment\Enum;

class ContainerContentEnum
{
    public const FILTER_BY_CHARACTER = 'character';
    public const array SPACE_CAPSULE_CONTENT = [
        ItemEnum::FUEL_CAPSULE => 1,
        ItemEnum::OXYGEN_CAPSULE => 1,
        ItemEnum::METAL_SCRAPS => 1,
        ItemEnum::PLASTIC_SCRAPS => 1,
    ];

    public const array COFFEE_THERMOS_CONTENT = [
        [
            'item' => GameRationEnum::COFFEE,
            'quantity' => 1,
            'weight' => 1,
        ],
    ];

    public const array ANNIVERSARY_GIFT_CONTENT = [
        [
            'item' => GearItemEnum::PLASTENITE_ARMOR,
            'quantity' => 1,
            'weight' => 10,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => 'chun',
        ],
        [
            'item' => GameRationEnum::ORGANIC_WASTE,
            'quantity' => 4,
            'weight' => 10,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => 'chun',
        ],
        [
            'item' => ItemEnum::KNIFE,
            'quantity' => 1,
            'weight' => 1,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => 'derek',
        ],
        [
            'item' => ItemEnum::BLASTER,
            'quantity' => 1,
            'weight' => 1,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => 'derek',
        ],
    ];
}
