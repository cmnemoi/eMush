import { Action } from "./Action";

export class Hunter {
    public id!: number;
    public key!: string;
    public name!: string;
    public description!: string;
    public health!: integer;
    public charges: integer|null;
    public actions: Array<Action>;

    constructor() {
        this.charges = null;
        this.actions = new Array<Action>();
    }

    public load(object: any): Hunter {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.description = object.description;
            this.health = object.health;
            this.charges = object.charges;
            object.actions.forEach((actionObject: any) => {
                this.actions.push((new Action).load(actionObject));
            });
        }

        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString : string): Hunter {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
