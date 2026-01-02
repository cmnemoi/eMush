<?php

namespace Mush\Place\Enum;

use Doctrine\Common\Collections\ArrayCollection;

class RoomEnum
{
    public const string BRIDGE = 'bridge';
    public const string ALPHA_BAY = 'alpha_bay';
    public const string BRAVO_BAY = 'bravo_bay';
    public const string ALPHA_BAY_2 = 'alpha_bay_2';
    public const string NEXUS = 'nexus';
    public const string MEDLAB = 'medlab';
    public const string LABORATORY = 'laboratory';
    public const string REFECTORY = 'refectory';
    public const string HYDROPONIC_GARDEN = 'hydroponic_garden';
    public const string ENGINE_ROOM = 'engine_room';
    public const string FRONT_ALPHA_TURRET = 'front_alpha_turret';
    public const string CENTRE_ALPHA_TURRET = 'centre_alpha_turret';
    public const string REAR_ALPHA_TURRET = 'rear_alpha_turret';
    public const string FRONT_BRAVO_TURRET = 'front_bravo_turret';
    public const string CENTRE_BRAVO_TURRET = 'centre_bravo_turret';
    public const string REAR_BRAVO_TURRET = 'rear_bravo_turret';
    public const string FRONT_CORRIDOR = 'front_corridor';
    public const string CENTRAL_CORRIDOR = 'central_corridor';
    public const string REAR_CORRIDOR = 'rear_corridor';
    public const string PLANET = 'planet';
    public const string ICARUS_BAY = 'icarus_bay';
    public const string ALPHA_DORM = 'alpha_dorm';
    public const string BRAVO_DORM = 'bravo_dorm';
    public const string FRONT_STORAGE = 'front_storage';
    public const string CENTER_ALPHA_STORAGE = 'center_alpha_storage';
    public const string REAR_ALPHA_STORAGE = 'rear_alpha_storage';
    public const string CENTER_BRAVO_STORAGE = 'center_bravo_storage';
    public const string REAR_BRAVO_STORAGE = 'rear_bravo_storage';
    public const string SPACE = 'space';
    public const string PATROL_SHIP_ALPHA_LONGANE = 'patrol_ship_alpha_longane';
    public const string PATROL_SHIP_ALPHA_JUJUBE = 'patrol_ship_alpha_jujube';
    public const string PATROL_SHIP_ALPHA_TAMARIN = 'patrol_ship_alpha_tamarin';
    public const string PATROL_SHIP_BRAVO_SOCRATE = 'patrol_ship_bravo_socrate';
    public const string PATROL_SHIP_BRAVO_EPICURE = 'patrol_ship_bravo_epicure';
    public const string PATROL_SHIP_BRAVO_PLANTON = 'patrol_ship_bravo_planton';
    public const string PATROL_SHIP_ALPHA_2_WALLIS = 'patrol_ship_alpha_2_wallis';
    public const string PASIPHAE = 'pasiphae';
    public const string PLANET_DEPTHS = 'planet_depths';
    public const string TABULATRIX_QUEUE = 'tabulatrix_queue';
    public const string NULL = '';

    public static function getAllDaedalusRooms(): array
    {
        return [
            self::BRIDGE,
            self::ALPHA_BAY,
            self::BRAVO_BAY,
            self::ALPHA_BAY_2,
            self::NEXUS,
            self::MEDLAB,
            self::LABORATORY,
            self::REFECTORY,
            self::HYDROPONIC_GARDEN,
            self::ENGINE_ROOM,
            self::FRONT_ALPHA_TURRET,
            self::CENTRE_ALPHA_TURRET,
            self::REAR_ALPHA_TURRET,
            self::FRONT_BRAVO_TURRET,
            self::CENTRE_BRAVO_TURRET,
            self::REAR_BRAVO_TURRET,
            self::FRONT_CORRIDOR,
            self::CENTRAL_CORRIDOR,
            self::REAR_CORRIDOR,
            self::ICARUS_BAY,
            self::ALPHA_DORM,
            self::BRAVO_DORM,
            self::FRONT_STORAGE,
            self::CENTER_ALPHA_STORAGE,
            self::REAR_ALPHA_STORAGE,
            self::CENTER_BRAVO_STORAGE,
            self::REAR_BRAVO_STORAGE,
        ];
    }

    public static function getStorages(): array
    {
        return [
            self::FRONT_STORAGE,
            self::CENTER_ALPHA_STORAGE,
            self::REAR_ALPHA_STORAGE,
            self::CENTER_BRAVO_STORAGE,
            self::REAR_BRAVO_STORAGE,
        ];
    }

    public static function getPatrolships(): ArrayCollection
    {
        return new ArrayCollection([
            self::PATROL_SHIP_ALPHA_LONGANE,
            self::PATROL_SHIP_ALPHA_JUJUBE,
            self::PATROL_SHIP_ALPHA_TAMARIN,
            self::PATROL_SHIP_BRAVO_SOCRATE,
            self::PATROL_SHIP_BRAVO_EPICURE,
            self::PATROL_SHIP_BRAVO_PLANTON,
            self::PATROL_SHIP_ALPHA_2_WALLIS,
            self::PASIPHAE,
        ]);
    }

    public static function getTurrets(): ArrayCollection
    {
        return new ArrayCollection([
            self::FRONT_ALPHA_TURRET,
            self::CENTRE_ALPHA_TURRET,
            self::REAR_ALPHA_TURRET,
            self::FRONT_BRAVO_TURRET,
            self::CENTRE_BRAVO_TURRET,
            self::REAR_BRAVO_TURRET,
        ]);
    }

    public static function getCorridors(): ArrayCollection
    {
        return new ArrayCollection([
            self::REAR_CORRIDOR,
            self::FRONT_CORRIDOR,
            self::CENTRAL_CORRIDOR,
        ]);
    }

    //  A result of 10 is a room next to you. 30 is quite close, more than 50 is going to be the other side of the ship.
    public static function getChebyshevDistance(string $roomNameA, string $roomNameB): int
    {
        $roomACoordinates = self::getRoomCoordinates($roomNameA);
        $roomBCoordinates = self::getRoomCoordinates($roomNameB);

        $x = abs($roomACoordinates[0] - $roomBCoordinates[0]);
        $y = abs($roomACoordinates[1] - $roomBCoordinates[1]);

        return (int) max($x, $y);
    }

    // First value is X, second value is Y. The values are arbitrary, they are meant to be used to approximate the distance between two rooms. Ex : To have an NPC avoid calculating path toward objects that are too far for him. Useful for complex behaviors
    public static function getRoomCoordinates(string $name): array
    {
        return match ($name) {
            self::REFECTORY => [20, 30],
            self::CENTRAL_CORRIDOR => [20, 20],
            self::FRONT_CORRIDOR => [20, 10],
            self::BRIDGE => [20, 0],
            self::FRONT_STORAGE => [30, 15],
            self::HYDROPONIC_GARDEN => [30, 10],
            self::MEDLAB => [10, 15],
            self::LABORATORY => [10, 10],
            self::FRONT_ALPHA_TURRET => [30, 0],
            self::FRONT_BRAVO_TURRET => [10, 0],
            self::CENTRE_ALPHA_TURRET => [40, 15],
            self::CENTRE_BRAVO_TURRET => [0, 15],
            self::ALPHA_BAY => [30, 40],
            self::BRAVO_BAY => [10, 40],
            self::CENTER_ALPHA_STORAGE => [40, 40],
            self::CENTER_BRAVO_STORAGE => [0, 40],
            self::REAR_CORRIDOR => [20, 50],
            self::ALPHA_DORM => [30, 45],
            self::BRAVO_DORM => [10, 45],
            self::NEXUS => [20, 60],
            self::ALPHA_BAY_2 => [30, 60],
            self::ICARUS_BAY => [10, 60],
            self::ENGINE_ROOM => [20, 70],
            self::REAR_ALPHA_STORAGE => [25, 60],
            self::REAR_BRAVO_STORAGE => [15, 60],
            self::REAR_ALPHA_TURRET => [30, 70],
            self::REAR_BRAVO_TURRET => [10, 70],
            default => [1000, 1000]
        };
    }
}
