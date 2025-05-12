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
            'priority' => 1,
        ],
        [
            'name' => SkinEnum::ANTENNA_SPATIAL_WAVE,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::ANTENNA,
            'priority' => 1,
        ],
        [
            'name' => SkinEnum::TURRET_TESLA,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::TURRET_COMMAND,
            'priority' => 1,
        ],
        [
            'name' => SkinEnum::TURRET_CHARGES,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::TURRET_COMMAND,
            'priority' => 2,
        ],
        [
            'name' => SkinEnum::COFFEE_MACHINE_FISSION,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::COFFEE_MACHINE,
            'priority' => 1,
        ],
        [
            'name' => SkinEnum::COFFEE_MACHINE_GUARANA,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::COFFEE_MACHINE,
            'priority' => 2,
        ],
        [
            'name' => SkinEnum::ICARUS_LARGE,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::ICARUS,
            'priority' => 2,
        ],
        [
            'name' => SkinEnum::ICARUS_THRUSTER,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::ICARUS,
            'priority' => 1,
        ],
        [
            'name' => SkinEnum::NERON_CORE_PARTICIPATION,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::NERON_CORE,
            'priority' => 1,
        ],
        [
            'name' => SkinEnum::NERON_PARTICIPATION_AUXILIARY_TERMINAL,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::AUXILIARY_TERMINAL,
            'priority' => 1,
        ],
        [
            'name' => SkinEnum::PATROL_SHIP_BLASTER,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::PATROL_SHIP,
            'priority' => 2,
        ],
        [
            'name' => SkinEnum::PATROL_SHIP_TELSATRON,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::PATROL_SHIP,
            'priority' => 1,
        ],
        [
            'name' => SkinEnum::PATROL_SHIP_PLAYER_SKIN_SLOT,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::PATROL_SHIP,
            'priority' => 10,
        ],
        [
            'name' => SkinEnum::ICARUS_PLAYER_SKIN_SLOT,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::ICARUS,
            'priority' => 10,
        ],
        [
            'name' => SkinEnum::SOFA_PLAYER_SKIN_SLOT,
            'skinableClass' => 'equipment',
            'skinableName' => EquipmentEnum::SWEDISH_SOFA,
            'priority' => 10,
        ],
        [
            'name' => SkinEnum::PILGRED_ACTIVE,
            'skinableClass' => 'place',
            'skinableName' => RoomEnum::ENGINE_ROOM,
            'priority' => 1,
        ],
        [
            'name' => SkinEnum::PLASMA_SHIELD_ACTIVE,
            'skinableClass' => 'place',
            'skinableName' => RoomEnum::ENGINE_ROOM,
            'priority' => 1,
        ],
        [
            'name' => SkinEnum::GARDEN_INCUBATOR,
            'skinableClass' => 'place',
            'skinableName' => RoomEnum::HYDROPONIC_GARDEN,
            'priority' => 1,
        ],
        [
            'name' => SkinEnum::GARDEN_LAMP,
            'skinableClass' => 'place',
            'skinableName' => RoomEnum::HYDROPONIC_GARDEN,
            'priority' => 2,
        ],
        [
            'name' => SkinEnum::KITCHEN_APERO_TABLE,
            'skinableClass' => 'place',
            'skinableName' => RoomEnum::REFECTORY,
            'priority' => 1,
        ],
        [
            'name' => SkinEnum::SCANNER_OVERCLOCKING,
            'skinableClass' => 'place',
            'skinableName' => EquipmentEnum::PLANET_SCANNER,
            'priority' => 1,
        ],
    ];
}
