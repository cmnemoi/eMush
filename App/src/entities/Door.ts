import { Action } from "@/entities/Action";
import { Status } from "@/entities/Status";
import { Equipment } from "@/entities/Equipment";

export class Door extends Equipment {
    public direction: string|null;

    constructor() {
        super();

        this.direction = null;
    }
    load(object : any) : Door {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.direction = object.direction;
            object.actions.forEach((actionObject : any) => {
                this.actions.push((new Action).load(actionObject));
            });
            object.statuses.forEach((statusObject : any) => {
                const status = (new Status()).load(statusObject);
                this.statuses.push(status);
                if (status.name === 'broken') {
                    this.isBroken = true;
                }
            });
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString : string): Door {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
