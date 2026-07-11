import { Action } from "@/entities/Action";
import { Status } from "@/entities/Status";

export type EquipmentData = {
    id?: number;
    key?: string;
    name?: string;
    description?: string;
    isBroken?: boolean;
    actions: Array<Parameters<Action["load"]>[0]>;
    statuses: Array<Parameters<Status["load"]>[0]>;
};

export class Equipment {
    public id: number;
    public key!: string;
    public name: string|null;
    public description: string|null;
    public actions: Array<Action>;
    public statuses: Array<Status>;
    public isBroken: boolean;

    constructor() {
        this.id = 0;
        this.name = null;
        this.actions = [];
        this.statuses = [];
        this.description = null;
        this.isBroken = false;
    }
    load(object : EquipmentData): Equipment {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.description = object.description;
            this.isBroken = object.isBroken;

            object.actions.forEach((actionObject: Parameters<Action["load"]>[0]) => {
                this.actions.push((new Action).load(actionObject));
            });

            object.statuses.forEach((statusObject: Parameters<Status["load"]>[0]) => {
                const status = (new Status()).load(statusObject);
                this.statuses.push(status);
            });
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: string): Equipment {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
