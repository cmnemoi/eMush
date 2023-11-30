<?php

namespace Mush\Daedalus\ConfigData;

use Mush\Place\Enum\RoomEnum;

/** @codeCoverageIgnore */
class DaedalusConfigData
{
    public static array $dataArray = [
        [
            'name' => 'default',
            'initOxygen' => 32,
            'initFuel' => 20,
            'initHull' => 100,
            'initShield' => -2,
            'initHunterPoints' => 40,
            'initCombustionChamberFuel' => 0,
            'maxOxygen' => 32,
            'maxFuel' => 32,
            'maxHull' => 100,
            'maxShield' => 100,
            'maxCombustionChamberFuel' => 9,
            'dailySporeNb' => 4,
            'nbMush' => 2,
            'cyclePerGameDay' => 1,
            'cycleLength' => 1,
            'randomItemPlaces' => 'default',
            'placeConfigs' => [
                RoomEnum::BRIDGE . '_default',
                RoomEnum::ALPHA_BAY . '_default',
                RoomEnum::BRAVO_BAY . '_default',
                RoomEnum::ALPHA_BAY_2 . '_default',
                RoomEnum::NEXUS . '_default',
                RoomEnum::MEDLAB . '_default',
                RoomEnum::LABORATORY . '_default',
                RoomEnum::REFECTORY . '_default',
                RoomEnum::HYDROPONIC_GARDEN . '_default',
                RoomEnum::ENGINE_ROOM . '_default',
                RoomEnum::FRONT_ALPHA_TURRET . '_default',
                RoomEnum::CENTRE_ALPHA_TURRET . '_default',
                RoomEnum::REAR_ALPHA_TURRET . '_default',
                RoomEnum::FRONT_BRAVO_TURRET . '_default',
                RoomEnum::CENTRE_BRAVO_TURRET . '_default',
                RoomEnum::REAR_BRAVO_TURRET . '_default',
                RoomEnum::FRONT_CORRIDOR . '_default',
                RoomEnum::CENTRAL_CORRIDOR . '_default',
                RoomEnum::REAR_CORRIDOR . '_default',
                RoomEnum::ICARUS_BAY . '_default',
                RoomEnum::ALPHA_DORM . '_default',
                RoomEnum::BRAVO_DORM . '_default',
                RoomEnum::FRONT_STORAGE . '_default',
                RoomEnum::CENTER_ALPHA_STORAGE . '_default',
                RoomEnum::REAR_ALPHA_STORAGE . '_default',
                RoomEnum::CENTER_BRAVO_STORAGE . '_default',
                RoomEnum::REAR_BRAVO_STORAGE . '_default',
                RoomEnum::SPACE . '_default',
                RoomEnum::PATROL_SHIP_ALPHA_2_WALLIS . '_default',
                RoomEnum::PATROL_SHIP_ALPHA_LONGANE . '_default',
                RoomEnum::PATROL_SHIP_ALPHA_JUJUBE . '_default',
                RoomEnum::PATROL_SHIP_ALPHA_TAMARIN . '_default',
                RoomEnum::PATROL_SHIP_BRAVO_EPICURE . '_default',
                RoomEnum::PATROL_SHIP_BRAVO_PLANTON . '_default',
                RoomEnum::PATROL_SHIP_BRAVO_SOCRATE . '_default',
                RoomEnum::PASIPHAE . '_default',
                RoomEnum::PLANET . '_default',
            ],
        ],
    ];
}
