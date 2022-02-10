import { Action } from "@/entities/Action";
import { Status } from "@/entities/Status";

export class Equipment {
    public id: number|null;
    public key!: string;
    public name: string|null;
    public description: string|null;
    public actions: Array<Action>;
    public statuses: Array<Status>;
    public isBroken: boolean;

    constructor() {
        this.id = null;
        this.name = null;
        this.actions = [];
        this.statuses = [];
        this.description = null;
        this.isBroken = false;
    }
    load(object :any): Equipment {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.description = object.description;

            object.actions.forEach((actionObject: any) => {
                this.actions.push((new Action).load(actionObject));
            });
            object.statuses.forEach((statusObject : any) => {
                const status = (new Status()).load(statusObject);
                this.statuses.push(status);

                if (status.key === 'broken') {
                    this.isBroken = true;
                }
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
