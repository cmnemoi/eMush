import { Item } from "@/entities/Item";
import { Door } from "@/entities/Door";
import { Player } from "@/entities/Player";
import { Equipment } from "@/entities/Equipment";
import { Status } from "@/entities/Status";

//@Hack: rooms that are handled by phaser and displayed with Phaser
export const PhaserRooms = [
    'medlab',
    'laboratory',
    'central_corridor',
    'front_storage',
    'front_corridor',
    'bravo_dorm',
    'alpha_dorm',
    'hydroponic_garden',
    'refectory',
    'center_alpha_storage',
    'center_bravo_storage',
    'rear_corridor',
    'nexus',
    'rear_alpha_storage',
    'rear_bravo_storage',
];

export class Room {
    public id: number|null;
    public key: string;
    public name?: string;
    public items: Array<Item>;
    public doors: Array<Door>;
    public statuses: Array<Status>;
    public equipments: Array<Equipment>;
    public players: Array<Player>;
    public isOnFire: boolean

    constructor() {
        this.id = null;
        this.key = 'none';
        this.items = [];
        this.doors = [];
        this.equipments = [];
        this.players = [];
        this.statuses = [];
        this.isOnFire = false;
    }
    load(object: any): Room {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            object.items.forEach((itemObject: any) => {
                const item = (new Item).load(itemObject);
                this.items.push(item);
            });
            object.doors.forEach((doorObject: any) => {
                const door = (new Door).load(doorObject);
                this.doors.push(door);
            });
            object.players.forEach((playerObject: any) => {
                const player = (new Player).load(playerObject);
                this.players.push(player);
            });

            object.equipments.forEach((equipmentObject:any) => {
                const equipment = (new Equipment()).load(equipmentObject);
                this.equipments.push(equipment);
            });
            object.statuses.forEach((statusObject:any) => {
                const status = (new Status()).load(statusObject);
                this.statuses.push(status);

                if (status.key === 'fire') {
                    this.isOnFire = true;
                }
            });
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString:string): Room {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
