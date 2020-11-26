import {Action} from "@/entities/Action";

export class Door {
    constructor() {
        this.id = null;
        this.key = null;
        this.name = null;
        this.actions = [];
        this.direction = null;
    }
    load = function(object) {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.direction = object.direction;
            object.actions.forEach((actionObject) => {
                this.actions.push((new Action).load(actionObject));
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