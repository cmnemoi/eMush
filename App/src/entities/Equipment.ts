import { Action } from "@/entities/Action";
import { Status } from "@/entities/Status";

export class Equipment {
    public id: number|null;
    public key: string|null;
    public name: string|null;
    public description: string|null;
    public actions: Array<Action>;
    public statuses: Array<Status>;

    constructor() {
        this.id = null;
        this.key = null;
        this.name = null;
        this.actions = [];
        this.statuses = [];
        this.description = null;
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
                let status = (new Status()).load(statusObject);
                this.statuses.push(status);
            });
        }
        return this;
    }
    jsonEncode() {
        return JSON.stringify(this);
    }
    decode(jsonString: string) {
        if (jsonString) {
            let object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
