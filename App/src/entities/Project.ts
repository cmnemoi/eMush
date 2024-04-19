type BonusSkill = {
    key: string;
    name: string;
    description: string;
}

export class Project {
    public key!: string;
    public name!: string;
    public description!: string;
    public progress!: string;
    public efficiency!: string;
    public bonusSkills!: BonusSkill[];

    load(object :any): Project {
        if (object) {
            this.key = object.key;
            this.name = object.name;
            this.description = object.description;
            this.progress = object.progress;
            this.efficiency = object.efficiency;
            this.bonusSkills = object.bonusSkills;
        }
        return this;
    }

    jsonEncode(): string {
        return JSON.stringify(this);
    }

    decode(jsonString : string): Project {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            return this.load(object);
        }

        return this;
    }
}