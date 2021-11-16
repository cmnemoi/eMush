import { Item } from "@/entities/Item";
import { Door } from "@/entities/Door";
import { Player } from "@/entities/Player";
import { Equipment } from "@/entities/Equipment";
import { Status } from "@/entities/Status";

//@Hack: rooms that are handled by phaser and displayed with Phaser
export const PhaserRooms = ['medlab', 'laboratory'];

export interface Room {
    id: number;
    key: string;
    name: string;
    items: Array<Item>;
    doors: Array<Door>;
    statuses: Array<Status>;
    equipments: Array<Equipment>;
    players: Array<Player>;
}
