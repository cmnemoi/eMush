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
                ItemEnum::CAMERA_ITEM,
                ItemEnum::CAMERA_ITEM,
                ItemEnum::CAMERA_ITEM,
                GearItemEnum::ADJUSTABLE_WRENCH,
                ItemEnum::KNIFE,
                ToolItemEnum::EXTINGUISHER,
                ToolItemEnum::EXTINGUISHER,
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
                ToolItemEnum::BLOCK_OF_POST_IT,
                'swedish_sofa_blueprint',
                'grenade_blueprint',
                'grenade',
                'blaster',
                'blaster',
                'rope',
                'rope',
                'drill',
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
