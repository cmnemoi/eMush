import { Action } from "@/entities/Action";
import { DaedalusOrientation } from "./DaedalusOrientation";

export class Terminal {
    public id!: number;
    public key!: string;
    public name!: string;
    public tips!: string;
    public actions: Action[] = [];
    public availableDaedalusOrientations: DaedalusOrientation[] = [];
    public currentDaedalusOrientation : string|null = null;

    public load(object: any): Terminal {
        if (object !== null && object !== undefined) {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.tips = object.tips;
            this.actions = object.actions;
            this.availableDaedalusOrientations = object.availableDaedalusOrientations;
            this.currentDaedalusOrientation = object.currentDaedalusOrientation;
        }
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: any): Terminal {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}