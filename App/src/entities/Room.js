import {Item} from "@/entities/Item";

export class Room {
    constructor() {
        this.id = null;
        this.items = [];
        this.key = null;
        this.name = null;
        this.doors = null;
    }
    load = function(object) {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.doors = object.doors;
            object.items.forEach((itemObject) => {
                let item = (new Item).load(itemObject)
                this.items.push(item);
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