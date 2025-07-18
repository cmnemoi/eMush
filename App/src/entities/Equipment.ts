import { Action } from "@/entities/Action";
import { Status } from "@/entities/Status";
import { Skin } from "@/entities/Skin";

export class Equipment {
    public id: number;
    public key!: string;
    public name: string|null;
    public description: string|null;
    public actions: Array<Action>;
    public statuses: Array<Status>;
    public isBroken: boolean;
    public skins: Array<Skin>;

    constructor() {
        this.id = 0;
        this.name = null;
        this.actions = [];
        this.statuses = [];
        this.description = null;
        this.isBroken = false;
        this.skins = [];
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
            object.skins.forEach((skinObject : any) => {
                const skin = (new Skin()).load(skinObject);
                this.skins.push(skin);
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
