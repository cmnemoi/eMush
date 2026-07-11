import { Action } from "@/entities/Action";
import { Status } from "@/entities/Status";
import { Equipment, EquipmentData } from "@/entities/Equipment";

type DoorData = EquipmentData & {
    direction?: string;
};

export class Door extends Equipment {
    public direction: string|null;

    constructor() {
        super();

        this.direction = null;
    }
    load(object : DoorData) : Door {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.direction = object.direction;
            this.isBroken = object.isBroken;

            object.actions.forEach((actionObject : Parameters<Action["load"]>[0]) => {
                this.actions.push((new Action).load(actionObject));
            });

            object.statuses.forEach((statusObject : Parameters<Status["load"]>[0]) => {
                const status = (new Status()).load(statusObject);
                this.statuses.push(status);
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
