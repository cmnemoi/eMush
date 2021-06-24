import { Item } from "@/entities/Item";
import { Door } from "@/entities/Door";
import { Player } from "@/entities/Player";
import { Equipment } from "@/entities/Equipment";
import { Status } from "@/entities/Status";

export class Room {
    public id: number|null;
    public key: string|null;
    public name: string|null;
    public items: Array<Item>;
    public doors: Array<Door>;
    public statuses: Array<Status>;
    public equipments: Array<Equipment>;
    public players: Array<Player>;

    constructor() {
        this.id = null;
        this.items = [];
        this.key = null;
        this.name = null;
        this.doors = [];
        this.equipments = [];
        this.players = [];
        this.statuses = [];
    }
    load(object: any) {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            object.items.forEach((itemObject: any) => {
                let item = (new Item).load(itemObject);
                this.items.push(item);
            });
            object.doors.forEach((doorObject: any) => {
                let door = (new Door).load(doorObject);
                this.doors.push(door);
            });
            object.players.forEach((playerObject: any) => {
                let player = (new Player).load(playerObject);
                this.players.push(player);
            });

            object.equipments.forEach((equipmentObject:any) => {
                let equipment = (new Equipment()).load(equipmentObject);
                this.equipments.push(equipment);
            });
            object.statuses.forEach((statusObject:any) => {
                let status = (new Status()).load(statusObject);
                this.statuses.push(status);
            });
        }
        return this;
    }
    jsonEncode() {
        return JSON.stringify(this);
    }
    decode(jsonString:string) {
        if (jsonString) {
            let object = JSON.parse(jsonString);
            this.load(object)
        }

        return this;
    }
}
