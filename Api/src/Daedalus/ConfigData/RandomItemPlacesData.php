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
            'name' => 'storage_default',
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
                ToolItemEnum::HACKER_KIT,
                ToolItemEnum::BLOCK_OF_POST_IT,
                'grenade',
                'blaster',
                'blaster',
                'rope',
                'rope',
                'drill',
                'quadrimetric_compass',
            ],
            'places' => [
                RoomEnum::FRONT_STORAGE,
                RoomEnum::CENTER_ALPHA_STORAGE,
                RoomEnum::REAR_ALPHA_STORAGE,
                RoomEnum::CENTER_BRAVO_STORAGE,
                RoomEnum::REAR_BRAVO_STORAGE,
            ],
        ],
        [
            'name' => 'daedalus_default',
            'items' => [
                ToolItemEnum::MEDIKIT,
                GearItemEnum::ANTIGRAV_SCOOTER,
                GearItemEnum::SPACESUIT,
                GearItemEnum::SPACESUIT,
                GearItemEnum::SPACESUIT,
                GearItemEnum::SPACESUIT,
                GearItemEnum::SPACESUIT,
                GearItemEnum::SPACESUIT,
                'swedish_sofa_blueprint',
                'grenade_blueprint',
                ItemEnum::OLD_T_SHIRT,
            ],
            'places' => [
                RoomEnum::FRONT_STORAGE,
                RoomEnum::CENTER_ALPHA_STORAGE,
                RoomEnum::REAR_ALPHA_STORAGE,
                RoomEnum::CENTER_BRAVO_STORAGE,
                RoomEnum::REAR_BRAVO_STORAGE,
                RoomEnum::FRONT_BRAVO_TURRET,
                RoomEnum::FRONT_ALPHA_TURRET,
                RoomEnum::CENTRE_BRAVO_TURRET,
                RoomEnum::CENTRE_ALPHA_TURRET,
                RoomEnum::REAR_BRAVO_TURRET,
                RoomEnum::REAR_ALPHA_TURRET,
                RoomEnum::ICARUS_BAY,
                RoomEnum::BRAVO_BAY,
                RoomEnum::ALPHA_BAY,
                RoomEnum::ALPHA_BAY_2,
                RoomEnum::FRONT_CORRIDOR,
                RoomEnum::CENTRAL_CORRIDOR,
                RoomEnum::REAR_CORRIDOR,
                RoomEnum::BRIDGE,
                RoomEnum::LABORATORY,
                RoomEnum::MEDLAB,
                RoomEnum::HYDROPONIC_GARDEN,
                RoomEnum::REFECTORY,
                RoomEnum::NEXUS,
                RoomEnum::ENGINE_ROOM,
                RoomEnum::BRAVO_DORM,
                RoomEnum::ALPHA_DORM,
            ],
        ],
    ];
}
