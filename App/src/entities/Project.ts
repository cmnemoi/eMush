import { ActionEnum } from "@/enums/action.enum";
import { Action } from "./Action";

type BonusSkill = {
    key: string;
    name: string;
    description: string;
}

export class Project {
    public id!: integer;
    public key!: string;
    public name!: string;
    public description!: string;
    public progress!: string;
    public efficiency!: string;
    public bonusSkills!: BonusSkill[];
    public repairPilgredAction!: Action;

    load(object :any): Project {
        if (object) {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.description = object.description;
            this.progress = object.progress;
            this.efficiency = object.efficiency;
            this.bonusSkills = object.bonusSkills;
            const repairPilgredActionData = object.actions.filter((action: any) => action.key === ActionEnum.REPAIR_PILGRED)[0];
            if (repairPilgredActionData) {
                this.repairPilgredAction = new Action().load(repairPilgredActionData);
            }
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
