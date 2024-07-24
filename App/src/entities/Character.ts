import { CharacterEnum } from "@/enums/character";

type AvailableSkill = {
    key: string;
    name: string;
    description: string;
};

export class Character {
    public key!: CharacterEnum;
    public name!: string;
    public abstract!: string;
    public description: string|null;
    public availableSkills!: AvailableSkill[];

    constructor() {
        this.description = null;
    }

    load(object: any): Character {
        if (typeof object !== "undefined") {
            this.key = object.key;
            this.name = object.value;
            this.abstract = object.abstract;
            this.description = object.description;
            this.availableSkills = object.availableSkills;
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
            this.availableSkills = object.availableSkills;
        }

        return this;
    }
}
