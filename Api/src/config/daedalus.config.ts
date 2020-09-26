import {RoomEnum} from '../enums/room.enum';

export default {
    maxPlayer: 16,
    initOxygen: 10,
    initFuel: 10,
    initHull: 100,
    initShield: -2,
    rooms: [
        {
            name: RoomEnum.BRIDGE,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.ALPHA_BAY,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.BRAVO_BAY,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.ALPHA_BAY_2,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.NEXUS,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.MEDLAB,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.LABORATORY,
            doors: [RoomEnum.FRONT_CORRIDOR, RoomEnum.MEDLAB],
            items: [],
        },
        {
            name: RoomEnum.REFECTORY,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.HYDROPONIC_GARDEN,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.ENGINE_ROOM,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.FRONT_ALPHA_TURRET,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.CENTRE_ALPHA_TURRET,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.REAR_ALPHA_TURRET,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.FRONT_BRAVO_TURRET,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.CENTRE_BRAVO_TURRET,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.REAR_BRAVO_TURRET,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.PATROLLER_16,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.PATROLLER_17,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.PATROLLER_18,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.PATROLLER_19,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.PATROLLER_20,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.PATROLLER_21,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.PATROLLER_22,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.PATROLLER_PASIPHAE,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.FRONT_CORRIDOR,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.CENTRAL_CORRIDOR,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.REAR_CORRIDOR,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.PLANET,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.ICARUS_BAY,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.ALPHA_DORM,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.BRAVO_DORM,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.FRONT_STORAGE,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.CENTER_ALPHA_STORAGE,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.REAR_ALPHA_STORAGE,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.CENTER_BRAVO_STORAGE,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.REAR_BRAVO_STORAGE,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.SPACE,
            doors: [],
            items: [],
        },
        {
            name: RoomEnum.GREAT_BEYOND,
            doors: [],
            items: [],
        },
    ],
};
