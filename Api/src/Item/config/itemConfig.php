<?php

use Mush\Item\Enum\ItemEnum;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Action\Enum\ActionEnum;

const ITEM_CONFIG = [
    [
        'name'=> ItemEnum::STANDARD_RATION,
        'type'=> ItemTypeEnum::RATION,
        'effects'=> [
            'consume' =>
            [
                'ap'=> 4,
                'mp'=> 0,
                'hp'=> 0,
                'morale'=> -1,
                'satiety'=> 4,
            ]
        ],
        'actions' => [ActionEnum::CONSUME],
        'isHeavy'=> false,
        'isDismantable'=> false,
        'isStackable'=> true,
        'isHideable'=> true,
        'isMoveable'=> true,
        'isDropable'=> true,
        'isTakeable'=> true,
        'isFireDestroyable'=> true,
        'isFireBreakable'=> false,
    ],
    [
        'name'=> ItemEnum::STAINPROOF_APRON,
        'type'=> ItemTypeEnum::GEAR,
        'effects' => [],
        'actions'=> [],
        'isHeavy'=> false,
        'isDismantable'=> false,
        'isStackable'=> false,
        'isHideable'=> true,
        'isMoveable'=> true,
        'isDropable'=> true,
        'isTakeable'=> true,
        'isFireDestroyable'=> false,
        'isFireBreakable'=> true,
    ],
    [
        'name'=> ItemEnum::KNIFE,
        'type'=> ItemTypeEnum::GEAR,
        'effects'=> [
            'attack' =>
            [
                'dmg' => [1, 5],
                'acc' => 0.6,
                'crit' => 0.05,
                'critMiss' => 0.05
            ]
        ],
        'actions' => [ActionEnum::ATTACK],
        'isHeavy'=> false,
        'isDismantable'=> false,
        'isStackable'=> true,
        'isHideable'=> true,
        'isMoveable'=> true,
        'isDropable'=> true,
        'isTakeable'=> true,
        'isFireDestroyable'=> true,
        'isFireBreakable'=> false,
    ],
    [   // objet exemple
        'name'=> ItemEnum::CHEESE_KNIFE,
        'type'=> ItemTypeEnum::GEAR,
        'effects'=> [
            'attack' =>
            [
                'dmg' => [1, 5],
                'acc' => 0.6,
                'crit' => 0.05,
                'critMiss' => 0.05
            ],
            'consume' =>
            [
                'ap' => 3,
                'satiety' => 2,
                'morale' => 1,
                'hp' => -1
            ]
        ],
        'actions' => [ActionEnum::ATTACK, ActionEnum::CONSUME],
        'isHeavy'=> false,
        'isDismantable'=> false,
        'isStackable'=> true,
        'isHideable'=> true,
        'isMoveable'=> true,
        'isDropable'=> true,
        'isTakeable'=> true,
        'isFireDestroyable'=> true,
        'isFireBreakable'=> false,
    ],
];
