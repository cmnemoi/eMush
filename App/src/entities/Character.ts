import { CharacterEnum } from "@/enums/character";

export class Character {
    public key!: CharacterEnum;
    public name!: string;
    public description: string|null;
    public skills!: Array<string>;

    constructor() {
        this.description = null;
    }

    load(object: any): Character {
        if (typeof object !== "undefined") {
            this.key = object.key;
            this.name = object.value;
            this.description = object.description;
            this.skills = object.skills;
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
            this.description = object.description;
            this.skills = object.skills;
        }

        return this;
    }
}
