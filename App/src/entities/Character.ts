import { CharacterEnum } from "@/enums/character";

export type SelectableSkill = {
    key: string;
    name: string;
    description: string;
};

export class Character {
    public key!: CharacterEnum;
    public name!: string;
    public abstract!: string;
    public description: string|null;
    public selectableHumanSkills!: SelectableSkill[];
    public selectableMushSkills!: SelectableSkill[];
    public humanSkillSlots!: integer;
    public mushSkillSlots!: integer;
    public humanLevel!: integer;
    public mushLevel!: integer;

    constructor() {
        this.description = null;
    }

    load(object: any): Character {
        if (typeof object !== "undefined") {
            this.key = object.key;
            this.name = object.value;
            this.abstract = object.abstract;
            this.description = object.description;
            this.selectableHumanSkills = object.selectableHumanSkills;
            this.selectableMushSkills = object.selectableMushSkills;
            this.humanSkillSlots = object.humanSkillSlots;
            this.mushSkillSlots = object.mushSkillSlots;
            this.humanLevel = object.humanLevel;
            this.mushLevel = object.mushLevel;
        }

        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: string): Character {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
