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
}
