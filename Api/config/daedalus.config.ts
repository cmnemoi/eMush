import {RoomEnum} from '../src/enums/room.enum';
import {DoorEnum} from '../src/enums/door.enum';
import {EquipmentEnum} from '../src/enums/equipment.enum';
import {ItemsEnum} from '../src/enums/items.enum';

export interface RoomConfig {
    name: string;
    doors: DoorEnum[];
    equipments: EquipmentEnum[];
    items: ItemsEnum[];
}
export interface DaedalusConfig {
    initOxygen: number;
    initFuel: number;
    initHull: number;
    initShield: number;
    randomItemPlace: {places: RoomEnum[]; items: ItemsEnum[]};
    rooms: RoomConfig[];
}

const Daedalus: DaedalusConfig = {
    initOxygen: 10,
    initFuel: 10,
    initHull: 100,
    initShield: -2,
    randomItemPlace: {
        places: [
            RoomEnum.FRONT_STORAGE,
            RoomEnum.CENTER_ALPHA_STORAGE,
            RoomEnum.CENTER_BRAVO_STORAGE,
            RoomEnum.REAR_ALPHA_STORAGE,
            RoomEnum.REAR_BRAVO_STORAGE,
        ],
        items: [ItemsEnum.STAINPROOF_APRON],
    },
    rooms: [
        {
            name: RoomEnum.BRIDGE,
            doors: [
                DoorEnum.BRIDGE_FRONT_ALPHA_TURRET,
                DoorEnum.BRIDGE_FRONT_BRAVO_TURRET,
                DoorEnum.FRONT_CORRIDOR_BRIDGE,
            ],
            equipments: [
                EquipmentEnum.COMMAND_TERMINAL,
                EquipmentEnum.COMMUNICATION_CENTER,
                EquipmentEnum.ASTRO_TERMINAL,
            ],
            items: [],
        },
        {
            name: RoomEnum.ALPHA_BAY,
            doors: [
                DoorEnum.ALPHA_BAY_ALPHA_DORM,
                DoorEnum.ALPHA_BAY_CENTER_ALPHA_STORAGE,
                DoorEnum.ALPHA_BAY_CENTRAL_ALPHA_TURRET,
                DoorEnum.ALPHA_BAY_CENTRAL_CORRIDOR,
                DoorEnum.ALPHA_BAY_ALPHA_BAY_2,
            ],
            equipments: [
                EquipmentEnum.PATROL_SHIP_ALPHA_JUJUBE,
                EquipmentEnum.PATROL_SHIP_ALPHA_LONGANE,
                EquipmentEnum.PATROL_SHIP_ALPHA_TAMARIN,
            ],
            items: [],
        },
        {
            name: RoomEnum.BRAVO_BAY,
            doors: [
                DoorEnum.BRAVO_BAY_BRAVO_DORM,
                DoorEnum.BRAVO_BAY_CENTER_BRAVO_STORAGE,
                DoorEnum.BRAVO_BAY_CENTRAL_BRAVO_TURRET,
                DoorEnum.BRAVO_BAY_CENTRAL_CORRIDOR,
                DoorEnum.BRAVO_BAY_REAR_CORRIDOR,
            ],
            equipments: [
                EquipmentEnum.PATROL_SHIP_BRAVO_EPICURE,
                EquipmentEnum.PATROL_SHIP_BRAVO_PLANTON,
                EquipmentEnum.PATROL_SHIP_BRAVO_SOCRATE,
            ],
            items: [],
        },
        {
            name: RoomEnum.ALPHA_BAY_2,
            doors: [
                DoorEnum.ALPHA_BAY_ALPHA_BAY_2,
                DoorEnum.ENGINE_ROOM_BAY_ALPHA_2,
                DoorEnum.REAR_CORRIDOR_BAY_ALPHA_2,
                DoorEnum.REAR_ALPHA_TURRET_BAY_ALPHA_2,
            ],
            equipments: [
                EquipmentEnum.PATROL_SHIP_ALPHA_2_wallis,
                EquipmentEnum.PASIPHAE,
            ],
            items: [],
        },
        {
            name: RoomEnum.NEXUS,
            doors: [DoorEnum.REAR_CORRIDOR_NEXUS],
            equipments: [
                EquipmentEnum.HEART_OF_NERON,
                EquipmentEnum.BIOS_TERMINAL,
            ],
            items: [],
        },
        {
            name: RoomEnum.MEDLAB,
            doors: [
                DoorEnum.MEDLAB_CENTRAL_BRAVO_TURRET,
                DoorEnum.MEDLAB_LABORATORY,
                DoorEnum.FRONT_CORRIDOR_MEDLAB,
            ],
            equipments: [EquipmentEnum.SURGICAL_PLOT, EquipmentEnum.BED],
            items: [],
        },
        {
            name: RoomEnum.LABORATORY,
            doors: [
                DoorEnum.FRONT_CORRIDOR_LABORATORY,
                DoorEnum.MEDLAB_LABORATORY,
            ],
            equipments: [
                EquipmentEnum.CRYO_MODULE,
                EquipmentEnum.GRAVITY_SIMULATOR,
                EquipmentEnum.RESEARCH_LABORATORY,
            ],
            items: [],
        },
        {
            name: RoomEnum.REFECTORY,
            doors: [DoorEnum.REFECTORY_CENTRAL_CORRIDOR],
            equipments: [EquipmentEnum.COFFEE_MACHINE, EquipmentEnum.KITCHEN],
            items: [
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
                ItemsEnum.STANDARD_RATION,
            ],
        },
        {
            name: RoomEnum.HYDROPONIC_GARDEN,
            doors: [
                DoorEnum.FRONT_CORRIDOR_GARDEN,
                DoorEnum.FRONT_STORAGE_GARDEN,
            ],
            equipments: [],
            items: [],
        },
        {
            name: RoomEnum.ENGINE_ROOM,
            doors: [
                DoorEnum.ENGINE_ROOM_BAY_ALPHA_2,
                DoorEnum.ENGINE_ROOM_BAY_ICARUS,
                DoorEnum.ENGINE_ROOM_REAR_ALPHA_STORAGE,
                DoorEnum.ENGINE_ROOM_REAR_BRAVO_STORAGE,
                DoorEnum.ENGINE_ROOM_REAR_ALPHA_TURRET,
                DoorEnum.ENGINE_ROOM_REAR_BRAVO_TURRET,
            ],
            equipments: [
                EquipmentEnum.ANTENNA,
                EquipmentEnum.COMBUSTION_CHAMBER,
                EquipmentEnum.PILGRED,
                EquipmentEnum.EMERGENCY_REACTOR,
                EquipmentEnum.REACTOR_LATERAL_ALPHA,
                EquipmentEnum.REACTOR_LATERAL_BRAVO,
                EquipmentEnum.PLANET_SCANNER,
            ],
            items: [],
        },
        {
            name: RoomEnum.FRONT_ALPHA_TURRET,
            doors: [
                DoorEnum.BRIDGE_FRONT_ALPHA_TURRET,
                DoorEnum.FRONT_CORRIDOR_FRONT_ALPHA_TURRET,
            ],
            equipments: [EquipmentEnum.SHOOTING_STATION],
            items: [],
        },
        {
            name: RoomEnum.CENTRE_ALPHA_TURRET,
            doors: [
                DoorEnum.FRONT_STORAGE_CENTRAL_ALPHA_TURRET,
                DoorEnum.ALPHA_BAY_CENTRAL_ALPHA_TURRET,
            ],
            equipments: [EquipmentEnum.SHOOTING_STATION],
            items: [],
        },
        {
            name: RoomEnum.REAR_ALPHA_TURRET,
            doors: [
                DoorEnum.REAR_ALPHA_TURRET_BAY_ALPHA_2,
                DoorEnum.ENGINE_ROOM_REAR_ALPHA_TURRET,
            ],
            equipments: [EquipmentEnum.SHOOTING_STATION],
            items: [],
        },
        {
            name: RoomEnum.FRONT_BRAVO_TURRET,
            doors: [
                DoorEnum.BRIDGE_FRONT_BRAVO_TURRET,
                DoorEnum.FRONT_CORRIDOR_FRONT_BRAVO_TURRET,
            ],
            equipments: [EquipmentEnum.SHOOTING_STATION],
            items: [],
        },
        {
            name: RoomEnum.CENTRE_BRAVO_TURRET,
            doors: [
                DoorEnum.MEDLAB_CENTRAL_BRAVO_TURRET,
                DoorEnum.BRAVO_BAY_CENTRAL_BRAVO_TURRET,
            ],
            equipments: [EquipmentEnum.SHOOTING_STATION],
            items: [],
        },
        {
            name: RoomEnum.REAR_BRAVO_TURRET,
            doors: [
                DoorEnum.REAR_BRAVO_TURRET_BAY_ICARUS,
                DoorEnum.ENGINE_ROOM_REAR_BRAVO_TURRET,
            ],
            equipments: [EquipmentEnum.SHOOTING_STATION],
            items: [],
        },
        {
            name: RoomEnum.FRONT_CORRIDOR,
            doors: [
                DoorEnum.FRONT_CORRIDOR_FRONT_ALPHA_TURRET,
                DoorEnum.FRONT_CORRIDOR_FRONT_BRAVO_TURRET,
                DoorEnum.FRONT_CORRIDOR_BRIDGE,
                DoorEnum.FRONT_CORRIDOR_GARDEN,
                DoorEnum.FRONT_CORRIDOR_FRONT_STORAGE,
                DoorEnum.FRONT_CORRIDOR_LABORATORY,
                DoorEnum.FRONT_CORRIDOR_MEDLAB,
                DoorEnum.FRONT_CORRIDOR_CENTRAL_CORRIDOR,
            ],
            equipments: [],
            items: [],
        },
        {
            name: RoomEnum.CENTRAL_CORRIDOR,
            doors: [
                DoorEnum.REFECTORY_CENTRAL_CORRIDOR,
                DoorEnum.FRONT_CORRIDOR_CENTRAL_CORRIDOR,
                DoorEnum.ALPHA_BAY_CENTRAL_CORRIDOR,
                DoorEnum.BRAVO_BAY_CENTRAL_CORRIDOR,
            ],
            equipments: [],
            items: [],
        },
        {
            name: RoomEnum.REAR_CORRIDOR,
            doors: [
                DoorEnum.REAR_CORRIDOR_NEXUS,
                DoorEnum.REAR_CORRIDOR_BAY_ALPHA_2,
                DoorEnum.REAR_CORRIDOR_ALPHA_DORM,
                DoorEnum.REAR_CORRIDOR_BRAVO_DORM,
                DoorEnum.REAR_CORRIDOR_BAY_ICARUS,
                DoorEnum.REAR_CORRIDOR_REAR_ALPHA_STORAGE,
                DoorEnum.REAR_CORRIDOR_REAR_BRAVO_STORAGE,
                DoorEnum.BRAVO_BAY_REAR_CORRIDOR,
            ],
            equipments: [],
            items: [],
        },
        {
            name: RoomEnum.ICARUS_BAY,
            doors: [
                DoorEnum.REAR_CORRIDOR_BAY_ICARUS,
                DoorEnum.REAR_BRAVO_TURRET_BAY_ICARUS,
                DoorEnum.ENGINE_ROOM_BAY_ICARUS,
            ],
            equipments: [EquipmentEnum.ICARUS],
            items: [],
        },
        {
            name: RoomEnum.ALPHA_DORM,
            doors: [
                DoorEnum.ALPHA_BAY_ALPHA_DORM,
                DoorEnum.REAR_CORRIDOR_ALPHA_DORM,
            ],
            equipments: [
                EquipmentEnum.BED,
                EquipmentEnum.BED,
                EquipmentEnum.BED,
                EquipmentEnum.SHOWER,
            ],
            items: [],
        },
        {
            name: RoomEnum.BRAVO_DORM,
            doors: [
                DoorEnum.BRAVO_BAY_BRAVO_DORM,
                DoorEnum.REAR_CORRIDOR_BRAVO_DORM,
            ],
            equipments: [
                EquipmentEnum.BED,
                EquipmentEnum.BED,
                EquipmentEnum.BED,
                EquipmentEnum.SHOWER,
            ],
            items: [],
        },
        {
            name: RoomEnum.FRONT_STORAGE,
            doors: [
                DoorEnum.FRONT_STORAGE_CENTRAL_ALPHA_TURRET,
                DoorEnum.FRONT_STORAGE_GARDEN,
                DoorEnum.FRONT_CORRIDOR_FRONT_STORAGE,
            ],
            equipments: [],
            items: [],
        },
        {
            name: RoomEnum.CENTER_ALPHA_STORAGE,
            doors: [DoorEnum.ALPHA_BAY_CENTER_ALPHA_STORAGE],
            equipments: [EquipmentEnum.OXYGEN_TANK],
            items: [],
        },
        {
            name: RoomEnum.REAR_ALPHA_STORAGE,
            doors: [
                DoorEnum.REAR_CORRIDOR_REAR_ALPHA_STORAGE,
                DoorEnum.ENGINE_ROOM_REAR_ALPHA_STORAGE,
            ],
            equipments: [EquipmentEnum.FUEL_TANK],
            items: [],
        },
        {
            name: RoomEnum.CENTER_BRAVO_STORAGE,
            doors: [DoorEnum.BRAVO_BAY_CENTER_BRAVO_STORAGE],
            equipments: [EquipmentEnum.OXYGEN_TANK],
            items: [],
        },
        {
            name: RoomEnum.REAR_BRAVO_STORAGE,
            doors: [
                DoorEnum.REAR_CORRIDOR_REAR_BRAVO_STORAGE,
                DoorEnum.ENGINE_ROOM_REAR_BRAVO_STORAGE,
            ],
            equipments: [EquipmentEnum.FUEL_TANK],
            items: [],
        },
        {
            name: RoomEnum.PLANET,
            doors: [],
            equipments: [],
            items: [],
        },
        {
            name: RoomEnum.SPACE,
            doors: [],
            equipments: [],
            items: [],
        },
        {
            name: RoomEnum.GREAT_BEYOND,
            doors: [],
            equipments: [],
            items: [],
        },
    ],
};

export default Daedalus;
