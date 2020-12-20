import {Action} from "@/entities/Action";
import {Status} from "@/entities/Status";

export class Equipment {
    constructor() {
        this.id = null;
        this.key = null;
        this.name = null;
        this.actions = [];
        this.statuses = [];
        this.description = null;
    }
    load(object) {
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
    jsonEncode() {
        return JSON.stringify(this);
    }
    decode(jsonString) {
        if (jsonString) {
            let object = JSON.parse(jsonString)
            this.load(object);
        }

        return this;
    }
}