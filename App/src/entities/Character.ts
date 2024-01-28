import { CharacterEnum } from "@/enums/character";

export class Character {
    public key!: CharacterEnum;
    public name!: string;
    public abstract!: string;
    public description: string|null;

    constructor() {
        this.description = null;
    }

    load(object: any): Character {
        if (typeof object !== "undefined") {
            this.key = object.key;
            this.name = object.value;
            this.abstract = object.abstract;
            this.description = object.description;
        }

        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: string): Character {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.key = object.key;
            this.name = object.value;
            this.abstract = object.abstract;
            this.description = object.description;
        }

        return this;
    }
}
