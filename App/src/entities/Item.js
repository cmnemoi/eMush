import {Action} from "@/entities/Action";
import {Status} from "@/entities/Status";

export class Item {
    constructor() {
        this.id = null;
        this.key = null;
        this.name = null;
        this.actions = [];
        this.statuses = [];
        this.description = null;
        this.number = 0;
    }
    load = function(object) {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.description = object.description;
            this.number = object.number;

            object.actions.forEach((actionObject) => {
                this.actions.push((new Action).load(actionObject));
            })
            object.statuses.forEach((statusObject) => {
                let status = (new Status()).load(statusObject)
                this.statuses.push(status);
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
            this.load(object);
        }

        return this;
    }
}