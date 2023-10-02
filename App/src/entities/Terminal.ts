import { Action } from "@/entities/Action";

export class Terminal {
    public id!: number;
    public key!: string;
    public name!: string;
    public tips!: string;
    public actions: Action[] = [];

    public load(object: any): Terminal {
        if (object) {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.tips = object.tips;
            this.actions = object.actions;
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