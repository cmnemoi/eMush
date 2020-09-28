import {RoomEnum} from '../src/enums/room.enum';
import {Door} from '../src/enums/door.enum';
import {Equipment} from '../src/enums/equipment.enum';

export default {
    initOxygen: 10,
    initFuel: 10,
    initHull: 100,
    initShield: -2,
    rooms: [
        {
            name: RoomEnum.BRIDGE,
            equipments: [
                Door.BRIDGE_FRONT_ALPHA_TURRET,
                Door.BRIDGE_FRONT_BRAVO_TURRET,
                Door.FRONT_CORRIDOR_BRIDGE,
                Equipment.COMMAND_TERMINAL,
                Equipment.COMMUNICATION_CENTER,
                Equipment.ASTRO_TERMINAL,
            ],
            items: [],
        },
        {
            name: RoomEnum.ALPHA_BAY,
            equipments: [
                Door.ALPHA_BAY_ALPHA_DORM,
                Door.ALPHA_BAY_CENTER_ALPHA_STORAGE,
                Door.ALPHA_BAY_CENTRAL_ALPHA_TURRET,
                Door.ALPHA_BAY_CENTRAL_CORRIDOR,
                Door.ALPHA_BAY_REAR_CORRIDOR,
                Door.ALPHA_BAY_ALPHA_BAY_2,
                Equipment.PATROL_SHIP_ALPHA_JUJUBE,
                Equipment.PATROL_SHIP_ALPHA_LONGANE,
                Equipment.PATROL_SHIP_ALPHA_TAMARIN,
            ],
            items: [],
        },
        {
            name: RoomEnum.BRAVO_BAY,
            equipments: [
                Door.BRAVO_BAY_BRAVO_DORM,
                Door.BRAVO_BAY_CENTER_BRAVO_STORAGE,
                Door.BRAVO_BAY_CENTRAL_BRAVO_TURRET,
                Door.BRAVO_BAY_CENTRAL_CORRIDOR,
                Door.BRAVO_BAY_REAR_CORRIDOR,
                Equipment.PATROL_SHIP_BRAVO_EPICURE,
                Equipment.PATROL_SHIP_BRAVO_PLANTON,
                Equipment.PATROL_SHIP_BRAVO_SOCRATE,
            ],
            items: [],
        },
        {
            name: RoomEnum.ALPHA_BAY_2,
            equipments: [
                Door.ALPHA_BAY_ALPHA_BAY_2,
                Door.ENGINE_ROOM_BAY_ALPHA_2,
                Door.REAR_CORRIDOR_BAY_ALPHA_2,
                Door.REAR_ALPHA_TURRET_BAY_ALPHA_2,
                Equipment.PATROL_SHIP_ALPHA_2_wallis,
                Equipment.PASIPHAE,
            ],
            items: [],
        },
        {
            name: RoomEnum.NEXUS,
            equipments: [
                Door.REAR_CORRIDOR_NEXUS,
                Equipment.HEART_OF_NERON,
                Equipment.BIOS_TERMINAL,
            ],
            items: [],
        },
        {
            name: RoomEnum.MEDLAB,
            equipments: [
                Door.MEDLAB_CENTRAL_BRAVO_TURRET,
                Door.MEDLAB_LABORATORY,
                Door.FRONT_CORRIDOR_MEDLAB,
                Equipment.SURGICAL_PLOT,
                Equipment.BED,
            ],
            items: [],
        },
        {
            name: RoomEnum.LABORATORY,
            equipments: [
                Door.FRONT_CORRIDOR_LABORATORY,
                Door.MEDLAB_LABORATORY,
                Equipment.CRYO_MODULE,
                Equipment.GRAVITY_SIMULATOR,
                Equipment.RESEARCH_LABORATORY,
            ],
            items: [],
        },
        {
            name: RoomEnum.REFECTORY,
            equipments: [
                Door.REFECTORY_CENTRAL_CORRIDOR,
                Equipment.COFFEE_MACHINE,
                Equipment.KITCHEN,
            ],
            items: [],
        },
        {
            name: RoomEnum.HYDROPONIC_GARDEN,
            equipments: [Door.FRONT_CORRIDOR_GARDEN, Door.FRONT_STORAGE_GARDEN],
            items: [],
        },
        {
            name: RoomEnum.ENGINE_ROOM,
            equipments: [
                Door.ENGINE_ROOM_BAY_ALPHA_2,
                Door.ENGINE_ROOM_BAY_ICARUS,
                Door.ENGINE_ROOM_REAR_ALPHA_STORAGE,
                Door.ENGINE_ROOM_REAR_BRAVO_STORAGE,
                Door.ENGINE_ROOM_REAR_ALPHA_TURRET,
                Door.ENGINE_ROOM_REAR_BRAVO_TURRET,
                Equipment.ANTENNA,
                Equipment.COMBUSTION_CHAMBER,
                Equipment.PILGRED,
                Equipment.EMERGENCY_REACTOR,
                Equipment.REACTOR_LATERAL_ALPHA,
                Equipment.REACTOR_LATERAL_BRAVO,
                Equipment.PLANET_SCANNER,
            ],
            items: [],
        },
        {
            name: RoomEnum.FRONT_ALPHA_TURRET,
            equipments: [
                Door.BRIDGE_FRONT_ALPHA_TURRET,
                Door.FRONT_CORRIDOR_FRONT_ALPHA_TURRET,
                Equipment.SHOOTING_STATION,
            ],
            items: [],
        },
        {
            name: RoomEnum.CENTRE_ALPHA_TURRET,
            equipments: [
                Door.FRONT_STORAGE_CENTRAL_ALPHA_TURRET,
                Door.ALPHA_BAY_CENTRAL_ALPHA_TURRET,
                Equipment.SHOOTING_STATION,
            ],
            items: [],
        },
        {
            name: RoomEnum.REAR_ALPHA_TURRET,
            equipments: [
                Door.REAR_ALPHA_TURRET_BAY_ALPHA_2,
                Door.ENGINE_ROOM_REAR_ALPHA_TURRET,
                Equipment.SHOOTING_STATION,
            ],
            items: [],
        },
        {
            name: RoomEnum.FRONT_BRAVO_TURRET,
            equipments: [
                Door.BRIDGE_FRONT_BRAVO_TURRET,
                Door.FRONT_CORRIDOR_FRONT_BRAVO_TURRET,
                Equipment.SHOOTING_STATION,
            ],
            items: [],
        },
        {
            name: RoomEnum.CENTRE_BRAVO_TURRET,
            equipments: [
                Door.MEDLAB_CENTRAL_BRAVO_TURRET,
                Door.BRAVO_BAY_CENTRAL_BRAVO_TURRET,
                Equipment.SHOOTING_STATION,
            ],
            items: [],
        },
        {
            name: RoomEnum.REAR_BRAVO_TURRET,
            equipments: [
                Door.REAR_BRAVO_TURRET_BAY_ICARUS,
                Door.ENGINE_ROOM_REAR_BRAVO_TURRET,
                Equipment.SHOOTING_STATION,
            ],
            items: [],
        },
        {
            name: RoomEnum.FRONT_CORRIDOR,
            equipments: [
                Door.FRONT_CORRIDOR_FRONT_ALPHA_TURRET,
                Door.FRONT_CORRIDOR_FRONT_BRAVO_TURRET,
                Door.FRONT_CORRIDOR_BRIDGE,
                Door.FRONT_CORRIDOR_GARDEN,
                Door.FRONT_CORRIDOR_FRONT_STORAGE,
                Door.FRONT_CORRIDOR_LABORATORY,
                Door.FRONT_CORRIDOR_MEDLAB,
                Door.FRONT_CORRIDOR_CENTRAL_CORRIDOR,
            ],
            items: [],
        },
        {
            name: RoomEnum.CENTRAL_CORRIDOR,
            equipments: [
                Door.REFECTORY_CENTRAL_CORRIDOR,
                Door.FRONT_CORRIDOR_CENTRAL_CORRIDOR,
                Door.ALPHA_BAY_CENTRAL_CORRIDOR,
                Door.BRAVO_BAY_CENTRAL_CORRIDOR,
            ],
            items: [],
        },
        {
            name: RoomEnum.REAR_CORRIDOR,
            equipments: [
                Door.REAR_CORRIDOR_NEXUS,
                Door.REAR_CORRIDOR_BAY_ALPHA_2,
                Door.REAR_CORRIDOR_ALPHA_DORM,
                Door.REAR_CORRIDOR_BRAVO_DORM,
                Door.REAR_CORRIDOR_BAY_ICARUS,
                Door.REAR_CORRIDOR_REAR_ALPHA_STORAGE,
                Door.REAR_CORRIDOR_REAR_BRAVO_STORAGE,
            ],
            items: [],
        },
        {
            name: RoomEnum.ICARUS_BAY,
            equipments: [],
            items: [
                Door.REAR_CORRIDOR_BAY_ICARUS,
                Door.REAR_BRAVO_TURRET_BAY_ICARUS,
                Door.ENGINE_ROOM_BAY_ICARUS,
                Equipment.ICARUS,
            ],
        },
        {
            name: RoomEnum.ALPHA_DORM,
            equipments: [
                Door.ALPHA_BAY_ALPHA_DORM,
                Door.REAR_CORRIDOR_ALPHA_DORM,
                Equipment.BED,
                Equipment.BED,
                Equipment.BED,
                Equipment.SHOWER,
            ],
            items: [],
        },
        {
            name: RoomEnum.BRAVO_DORM,
            equipments: [
                Door.BRAVO_BAY_BRAVO_DORM,
                Door.REAR_CORRIDOR_BRAVO_DORM,
                Equipment.BED,
                Equipment.BED,
                Equipment.BED,
                Equipment.SHOWER,
            ],
            items: [],
        },
        {
            name: RoomEnum.FRONT_STORAGE,
            equipments: [
                Door.FRONT_STORAGE_CENTRAL_ALPHA_TURRET,
                Door.FRONT_STORAGE_GARDEN,
                Door.FRONT_CORRIDOR_FRONT_STORAGE,
            ],
            items: [],
        },
        {
            name: RoomEnum.CENTER_ALPHA_STORAGE,
            equipments: [
                Door.ALPHA_BAY_CENTER_ALPHA_STORAGE,
                Equipment.OXYGEN_TANK,
            ],
            items: [],
        },
        {
            name: RoomEnum.REAR_ALPHA_STORAGE,
            equipments: [
                Door.REAR_CORRIDOR_REAR_ALPHA_STORAGE,
                Door.ENGINE_ROOM_REAR_ALPHA_STORAGE,
                Equipment.FUEL_TANK,
            ],
            items: [],
        },
        {
            name: RoomEnum.CENTER_BRAVO_STORAGE,
            equipments: [
                Door.BRAVO_BAY_CENTER_BRAVO_STORAGE,
                Equipment.OXYGEN_TANK,
            ],
            items: [],
        },
        {
            name: RoomEnum.REAR_BRAVO_STORAGE,
            equipments: [
                Door.REAR_CORRIDOR_REAR_BRAVO_STORAGE,
                Door.ENGINE_ROOM_REAR_BRAVO_STORAGE,
                Equipment.FUEL_TANK,
            ],
            items: [],
        },
        {
            name: RoomEnum.PLANET,
            equipments: [],
            items: [],
        },
        {
            name: RoomEnum.SPACE,
            equipments: [],
            items: [],
        },
        {
            name: RoomEnum.GREAT_BEYOND,
            equipments: [],
            items: [],
        },
    ],
};
