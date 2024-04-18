<?php

namespace Mush\Place\Enum;

class DoorEnum
{
    public const BRIDGE_FRONT_ALPHA_TURRET = 'bridge_front_alpha_turret';
    public const BRIDGE_FRONT_BRAVO_TURRET = 'bridge_front_bravo_turret';
    public const FRONT_CORRIDOR_FRONT_ALPHA_TURRET = 'front_corridor_front_alpha_turret';
    public const FRONT_CORRIDOR_FRONT_BRAVO_TURRET = 'front_corridor_front_beta_turret';
    public const FRONT_CORRIDOR_BRIDGE = 'front_corridor_bridge';
    public const FRONT_CORRIDOR_LABORATORY = 'front_corridor_laboratory';
    public const FRONT_CORRIDOR_MEDLAB = 'front_corridor_medlab';
    public const FRONT_CORRIDOR_GARDEN = 'front_corridor_garden';
    public const FRONT_CORRIDOR_FRONT_STORAGE = 'front_corridor_front_storage';
    public const FRONT_CORRIDOR_CENTRAL_CORRIDOR = 'front_corridor_central_corridor';
    public const MEDLAB_LABORATORY = 'medlab_laboratory';
    public const MEDLAB_CENTRAL_BRAVO_TURRET = 'medlab_central_bravo_turret';
    public const FRONT_STORAGE_GARDEN = 'front_storage_garden';
    public const FRONT_STORAGE_CENTRAL_ALPHA_TURRET = 'front_storage_central_alpha_turret';
    public const ALPHA_BAY_CENTRAL_ALPHA_TURRET = 'alpha_bay_central_alpha_turret';
    public const ALPHA_BAY_CENTRAL_CORRIDOR = 'alpha_bay_central_corridor';
    public const ALPHA_BAY_CENTER_ALPHA_STORAGE = 'alpha_bay_center_alpha_storage';
    public const ALPHA_BAY_ALPHA_DORM = 'alpha_bay_alpha_dorm';
    public const ALPHA_BAY_ALPHA_BAY_2 = 'alpha_bay_alpha_bay_2';
    public const BRAVO_BAY_CENTRAL_BRAVO_TURRET = 'bravo_bay_central_bravo_turret';
    public const BRAVO_BAY_CENTRAL_CORRIDOR = 'bravo_bay_central_corridor';
    public const BRAVO_BAY_CENTER_BRAVO_STORAGE = 'bravo_bay_center_bravo_storage';
    public const BRAVO_BAY_BRAVO_DORM = 'bravo_bay_bravo_dorm';
    public const BRAVO_BAY_REAR_CORRIDOR = 'bravo_bay_rear_corridor';
    public const REFECTORY_CENTRAL_CORRIDOR = 'refectory_central_corridor';
    public const REAR_CORRIDOR_ALPHA_DORM = 'rear_corridor_alpha_dorm';
    public const REAR_CORRIDOR_BRAVO_DORM = 'rear_corridor_bravo_dorm';
    public const REAR_CORRIDOR_BAY_ALPHA_2 = 'rear_corridor_bay_alpha_2';
    public const REAR_CORRIDOR_NEXUS = 'rear_corridor_nexus';
    public const REAR_CORRIDOR_BAY_ICARUS = 'rear_corridor_bay_icarus';
    public const REAR_CORRIDOR_REAR_ALPHA_STORAGE = 'rear_corridor_rear_alpha_storage';
    public const REAR_CORRIDOR_REAR_BRAVO_STORAGE = 'rear_corridor_rear_bravo_storage';
    public const ENGINE_ROOM_BAY_ALPHA_2 = 'engine_room_bay_alpha_2';
    public const ENGINE_ROOM_BAY_ICARUS = 'engine_room_bay_icarus';
    public const ENGINE_ROOM_REAR_ALPHA_STORAGE = 'engine_room_rear_alpha_storage';
    public const ENGINE_ROOM_REAR_BRAVO_STORAGE = 'engine_room_rear_bravo_storage';
    public const ENGINE_ROOM_REAR_ALPHA_TURRET = 'engine_room_rear_alpha_turret';
    public const ENGINE_ROOM_REAR_BRAVO_TURRET = 'engine_room_rear_bravo_turret';
    public const REAR_ALPHA_TURRET_BAY_ALPHA_2 = 'rear_alpha_turret_bay_alpha_2';
    public const REAR_BRAVO_TURRET_BAY_ICARUS = 'rear_bravo_turret_bay_icarus';

    public static function getAllDoors(): array
    {
        return [
            self::BRIDGE_FRONT_ALPHA_TURRET,
            self::BRIDGE_FRONT_BRAVO_TURRET,
            self::FRONT_CORRIDOR_FRONT_ALPHA_TURRET,
            self::FRONT_CORRIDOR_FRONT_BRAVO_TURRET,
            self::FRONT_CORRIDOR_BRIDGE,
            self::FRONT_CORRIDOR_LABORATORY,
            self::FRONT_CORRIDOR_MEDLAB,
            self::FRONT_CORRIDOR_GARDEN,
            self::FRONT_CORRIDOR_FRONT_STORAGE,
            self::FRONT_STORAGE_CENTRAL_ALPHA_TURRET,
            self::FRONT_CORRIDOR_CENTRAL_CORRIDOR,
            self::MEDLAB_LABORATORY,
            self::FRONT_STORAGE_GARDEN,
            self::REFECTORY_CENTRAL_CORRIDOR,
            self::MEDLAB_CENTRAL_BRAVO_TURRET,
            self::BRAVO_BAY_CENTRAL_BRAVO_TURRET,
            self::BRAVO_BAY_CENTRAL_CORRIDOR,
            self::BRAVO_BAY_CENTER_BRAVO_STORAGE,
            self::BRAVO_BAY_REAR_CORRIDOR,
            self::BRAVO_BAY_BRAVO_DORM,
            self::ALPHA_BAY_CENTER_ALPHA_STORAGE,
            self::ALPHA_BAY_ALPHA_DORM,
            self::ALPHA_BAY_CENTRAL_ALPHA_TURRET,
            self::ALPHA_BAY_CENTRAL_CORRIDOR,
            self::ALPHA_BAY_ALPHA_BAY_2,
            self::REAR_CORRIDOR_ALPHA_DORM,
            self::REAR_CORRIDOR_BRAVO_DORM,
            self::REAR_CORRIDOR_NEXUS,
            self::REAR_CORRIDOR_BAY_ALPHA_2,
            self::REAR_CORRIDOR_BAY_ICARUS,
            self::REAR_CORRIDOR_REAR_ALPHA_STORAGE,
            self::REAR_CORRIDOR_REAR_BRAVO_STORAGE,
            self::REAR_BRAVO_TURRET_BAY_ICARUS,
            self::REAR_ALPHA_TURRET_BAY_ALPHA_2,
            self::ENGINE_ROOM_BAY_ICARUS,
            self::ENGINE_ROOM_REAR_ALPHA_STORAGE,
            self::ENGINE_ROOM_REAR_BRAVO_STORAGE,
            self::ENGINE_ROOM_BAY_ALPHA_2,
            self::ENGINE_ROOM_REAR_ALPHA_TURRET,
            self::ENGINE_ROOM_REAR_BRAVO_TURRET,
        ];
    }

    public static function isBreakable(string $doorName): bool
    {
        return \in_array($doorName, [
            self::BRIDGE_FRONT_BRAVO_TURRET,
            self::FRONT_CORRIDOR_FRONT_BRAVO_TURRET,
            self::MEDLAB_LABORATORY,
            self::FRONT_CORRIDOR_GARDEN,
            self::FRONT_CORRIDOR_FRONT_STORAGE,
            self::FRONT_STORAGE_CENTRAL_ALPHA_TURRET,
            self::BRAVO_BAY_CENTRAL_BRAVO_TURRET,
            self::REFECTORY_CENTRAL_CORRIDOR,
            self::BRAVO_BAY_CENTER_BRAVO_STORAGE,
            self::ALPHA_BAY_CENTER_ALPHA_STORAGE,
            self::ALPHA_BAY_ALPHA_DORM,
            self::REAR_CORRIDOR_ALPHA_DORM,
            self::REAR_CORRIDOR_BRAVO_DORM,
            self::REAR_CORRIDOR_NEXUS,
            self::BRAVO_BAY_REAR_CORRIDOR,
            self::REAR_BRAVO_TURRET_BAY_ICARUS,
            self::ENGINE_ROOM_REAR_ALPHA_STORAGE,
            self::ENGINE_ROOM_REAR_BRAVO_STORAGE,
            self::ENGINE_ROOM_BAY_ALPHA_2,
            self::ENGINE_ROOM_REAR_ALPHA_TURRET,
        ], true);
    }
}
