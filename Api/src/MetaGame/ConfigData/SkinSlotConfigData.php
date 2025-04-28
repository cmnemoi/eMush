<?php

namespace Mush\MetaGame\ConfigData;

use Mush\Equipment\Enum\EquipmentEnum;
use Mush\MetaGame\Enum\SkinEnum;
use Mush\Place\Enum\RoomEnum;

/** @codeCoverageIgnore */
class SkinSlotConfigData
{
    public static array $dataArray = [
        [
            'name' => SkinEnum::KITCHEN_APERO,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::KITCHEN,
        ],
        [
            'name' => SkinEnum::SCANNER_OVERCLOCKING,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::PLANET_SCANNER,
        ],
        [
            'name' => SkinEnum::ANTENNA_SPATIAL_WAVE,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::ANTENNA,
        ],
        [
            'name' => SkinEnum::TURRET_TESLA,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::TURRET_COMMAND,
        ],
        [
            'name' => SkinEnum::TURRET_CHARGES,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::TURRET_COMMAND,
        ],
        [
            'name' => SkinEnum::COFFEE_MACHINE_FISSION,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::COFFEE_MACHINE,
        ],
        [
            'name' => SkinEnum::COFFEE_MACHINE_GUARANA,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::COFFEE_MACHINE,
        ],
        [
            'name' => SkinEnum::ICARUS_LARGE,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::ICARUS,
        ],
        [
            'name' => SkinEnum::ICARUS_THRUSTER,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::ICARUS,
        ],
        [
            'name' => SkinEnum::NERON_CORE_PARTICIPATION,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::NERON_CORE,
        ],
        [
            'name' => SkinEnum::NERON_PARTICIPATION_AUXILIARY_TERMINAL,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::AUXILIARY_TERMINAL,
        ],
        [
            'name' => SkinEnum::PATROL_SHIP_BLASTER,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::PATROL_SHIP,
        ],
        [
            'name' => SkinEnum::PATROL_SHIP_TELSATRON,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::PATROL_SHIP,
        ],
        [
            'name' => SkinEnum::PATROL_SHIP_PLAYER_SKIN_SLOT,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::PATROL_SHIP,
        ],
        [
            'name' => SkinEnum::ICARUS_PLAYER_SKIN_SLOT,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::ICARUS,
        ],
        [
            'name' => SkinEnum::SOFA_PLAYER_SKIN_SLOT,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::SWEDISH_SOFA,
        ],
        [
            'name' => SkinEnum::PILGRED_ACTIVE,
            'skinableClass' => 'place',
            'skinableName' => RoomEnum::ENGINE_ROOM,
        ],
        [
            'name' => SkinEnum::PLASMA_SHIELD_ACTIVE,
            'skinableClass' => 'place',
            'skinableName' => RoomEnum::ENGINE_ROOM,
        ],
        [
            'name' => SkinEnum::GARDEN_INCUBATOR,
            'skinableClass' => 'place',
            'skinableName' => RoomEnum::HYDROPONIC_GARDEN,
        ],
        [
            'name' => SkinEnum::GARDEN_LAMP,
            'skinableClass' => 'place',
            'skinableName' => RoomEnum::HYDROPONIC_GARDEN,
        ],
        [
            'name' => SkinEnum::KITCHEN_APERO_TABLE,
            'skinableClass' => 'place',
            'skinableName' => RoomEnum::REFECTORY,
        ],
    ];
}
