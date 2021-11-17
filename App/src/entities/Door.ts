import { Action } from "@/entities/Action";
import { Status } from "@/entities/Status";

export class Door {
    public id: number|null;
    public key: string|null;
    public name!: string;
    public actions: Array<Action>;
    public direction: string|null;
    public statuses: Array<Status>;
    public isBroken: boolean;

    constructor() {
        this.id = null;
        this.key = null;
        this.actions = [];
        this.direction = null;
        this.statuses = [];
        this.isBroken = false;
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
