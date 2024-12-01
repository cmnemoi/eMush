<?php

namespace Mush\Equipment\Enum;

class ContainerContentEnum
{
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
            'weight' => 3,
        ],
    ];

    public const array XMAS_GIFT_CONTENT = [
        [
            'item' => GearItemEnum::PLASTENITE_ARMOR,
            'quantity' => 1,
            'weight' => 1,
        ],
        [
            'item' => GameRationEnum::ORGANIC_WASTE,
            'quantity' => 4,
            'weight' => 1,
        ],
        [
            'item' => ItemEnum::KNIFE,
            'quantity' => 1,
            'weight' => 1,
        ],
        [
            'item' => ItemEnum::BLASTER,
            'quantity' => 1,
            'weight' => 1,
        ],
    ];
}
