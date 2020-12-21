import {Item} from "@/entities/Item";
import {Door} from "@/entities/Door";
import {Player} from "@/entities/Player";
import {Equipment} from "@/entities/Equipment";

export class Room {
    constructor() {
        this.id = null;
        this.items = [];
        this.key = null;
        this.name = null;
        this.doors = [];
        this.equipments = [];
        this.players = [];
    }
    load = function(object) {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            object.items.forEach((itemObject) => {
                let item = (new Item).load(itemObject)
                this.items.push(item);
            })
            object.doors.forEach((doorObject) => {
                let door = (new Door).load(doorObject)
                this.doors.push(door);
            })
            object.players.forEach((playerObject) => {
                let player = (new Player).load(playerObject)
                this.players.push(player);
            })

            object.equipments.forEach((equipmentObject) => {
                let equipment = (new Equipment()).load(equipmentObject)
                this.equipments.push(equipment);
            })
        }
        return this;
    }
    jsonEncode = function() {
        return JSON.stringify(this);
    }
    decode = function(jsonString) {
        if (jsonString) {
            let object = JSON.parse(jsonString)
            this.id = object.id;
            this.roomKey = object.roomKey;
            this.roomName = object.roomName;
            this.items = object.items;
            this.doors = object.doors;
        }

        return this;
    }
}