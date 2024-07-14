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
    public lore!: string;
    public progress!: string;
    public efficiency!: string;
    public efficiencyTooltipHeader!: string;
    public efficiencyTooltipText!: string;
    public bonusSkills!: BonusSkill[];
    public isLastAdvancedProject!: boolean;
    public repairPilgredAction: Action|null = null;
    public participateAction: Action|null = null;

    load(object :any): Project {
        if (object) {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.description = object.description;
            this.lore = object.lore;
            this.progress = object.progress;
            this.efficiency = object.efficiency;
            this.efficiencyTooltipHeader = object.efficiencyTooltipHeader;
            this.efficiencyTooltipText = object.efficiencyTooltipText;
            this.bonusSkills = object.bonusSkills;
            this.isLastAdvancedProject = object.isLastAdvancedProject;
            const repairPilgredActionData = object.actions.filter((action: any) => action.key === ActionEnum.REPAIR_PILGRED)[0];
            const participateActionData = object.actions.filter((action: any) => action.key === ActionEnum.PARTICIPATE)[0];
            if (repairPilgredActionData) {
                this.repairPilgredAction = new Action().load(repairPilgredActionData);
            }
            if (participateActionData) {
                this.participateAction = new Action().load(participateActionData);
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
