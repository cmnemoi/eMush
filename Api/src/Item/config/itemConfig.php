<?php

use Mush\Item\Enum\ItemEnum;
use Mush\Item\Enum\ItemTypeEnum;

const ITEM_CONFIG = [
    [
        'name'=> ItemEnum::STANDARD_RATION,
        'type'=> ItemTypeEnum::RATION,
        'effects'=> [
            'actionPoint'=> 4,
            'movementPoint'=> 0,
            'healthPoint'=> 0,
            'moralPoint'=> -1,
            'satiety'=> 4,
        ],
        'isHeavy'=> false,
        'isDismantable'=> false,
        'isStackable'=> true,
        'isHideable'=> true,
        'isMoveable'=> true,
        'isFireDestroyable'=> true,
        'isFireBreakable'=> false,
    ],
    [
        'name'=> ItemEnum::STAINPROOF_APRON,
        'type'=> ItemTypeEnum::GEAR,
        'actions'=> [],
        'isHeavy'=> false,
        'isDismantable'=> false,
        'isStackable'=> false,
        'isHideable'=> true,
        'isMoveable'=> true,
        'isFireDestroyable'=> false,
        'isFireBreakable'=> true,
    ],

];
