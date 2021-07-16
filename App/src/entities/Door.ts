import { Action } from "@/entities/Action";

export class Door {
    public id: number|null;
    public key: string|null;
    public name!: string;
    public actions: Array<Action>;
    public direction: string|null;

    constructor() {
        this.id = null;
        this.key = null;
        this.actions = [];
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
