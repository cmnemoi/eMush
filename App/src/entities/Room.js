import {Item} from "@/entities/Item";
import {Door} from "@/entities/Door";

export class Room {
    constructor() {
        this.id = null;
        this.items = [];
        this.key = null;
        this.name = null;
        this.doors = [];
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