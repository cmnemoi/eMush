export class Action {
    constructor() {
        this.id = null;
        this.key = null;
        this.canExecute = null;
        this.name = null;
        this.description = null;
        this.actionPointCost = null;
        this.movementPointCost = null;
        this.successRate = null;
    }
    load = function(object) {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.canExecute = object.canExecute;
            this.name = object.name;
            this.description = object.description;
            this.actionPointCost = object.actionPointCost;
            this.movementPointCost = object.movementPointCost;
            this.successRate = object.successRate;
        }
        return this;
    }
    jsonEncode = function() {
        return JSON.stringify(this);
    }
    decode = function(jsonString) {
        if (jsonString) {
            let object = JSON.parse(jsonString);
            this.id = object.id;
            this.roomKey = object.roomKey;
            this.roomName = object.roomName;
            this.items = object.items;
            this.doors = object.doors;
        }

        return this;
    }
}
