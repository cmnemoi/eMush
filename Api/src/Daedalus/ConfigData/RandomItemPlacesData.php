<?php

namespace Mush\Daedalus\ConfigData;

use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Place\Enum\RoomEnum;

/** @codeCoverageIgnore */
class RandomItemPlacesData
{
    public static array $dataArray = [
        [
            'name' => 'default',
            'items' => [
                GearItemEnum::PLASTENITE_ARMOR,
                ItemEnum::QUADRIMETRIC_COMPASS,
                ItemEnum::CAMERA_ITEM,
                ItemEnum::CAMERA_ITEM,
                ItemEnum::CAMERA_ITEM,
                GearItemEnum::ADJUSTABLE_WRENCH,
                ItemEnum::ROPE,
                ItemEnum::ROPE,
                ItemEnum::KNIFE,
                ToolItemEnum::EXTINGUISHER,
                ToolItemEnum::EXTINGUISHER,
                ItemEnum::DRILL,
                GearItemEnum::PROTECTIVE_GLOVES,
                ItemEnum::HYDROPOT,
                ItemEnum::HYDROPOT,
                ToolItemEnum::DUCT_TAPE,
                GearItemEnum::SOAP,
                GearItemEnum::STAINPROOF_APRON,
                ToolItemEnum::MEDIKIT,
                GearItemEnum::ANTIGRAV_SCOOTER,
                GearItemEnum::SPACESUIT,
                GearItemEnum::SPACESUIT,
                GearItemEnum::SPACESUIT,
                GearItemEnum::SPACESUIT,
                GearItemEnum::SPACESUIT,
                GearItemEnum::SPACESUIT,
                ToolItemEnum::HACKER_KIT,
            ],
            'places' => [
                RoomEnum::FRONT_STORAGE,
                RoomEnum::CENTER_ALPHA_STORAGE,
                RoomEnum::REAR_ALPHA_STORAGE,
                RoomEnum::CENTER_BRAVO_STORAGE,
                RoomEnum::REAR_BRAVO_STORAGE,
            ],
        ],
    ];
}
