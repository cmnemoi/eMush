<?php

use Mush\Item\Enum\ItemEnum;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Action\Enum\ActionEnum;

const ITEMCONFIG = [
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

];
