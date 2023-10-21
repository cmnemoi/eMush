import { Action } from "@/entities/Action";
import { TerminalSectionTitles } from "@/entities/TerminalSectionTitles";
import { TerminalInfos } from "@/entities/TerminalInfos";

export class Terminal {
    public id!: number;
    public key!: string;
    public name!: string;
    public tips!: string;
    public actions: Action[] = [];
    public sectionTitles!: TerminalSectionTitles;
    public infos!: TerminalInfos;

    public load(object: any): Terminal {
        if (object) {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.tips = object.tips;
            this.actions = object.actions;
            this.sectionTitles = new TerminalSectionTitles().load(object.sectionTitles);
            this.infos = new TerminalInfos().load(object.infos);
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

    public getActionByKey(key: string): Action | null {
        return this.actions.find(action => action.key === key) || null;
    }
}