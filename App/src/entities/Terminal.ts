import { Action } from "@/entities/Action";
import { TerminalSectionTitles } from "@/entities/TerminalSectionTitles";
import { TerminalInfos } from "@/entities/TerminalInfos";
import { TerminalButtons } from "@/entities/TerminalButtons";
import { Project } from "@/entities/Project";
import { Item } from "./Item";
import { RebelBase } from "@/entities/RebelBase";
export class Terminal {
    public id!: number;
    public key!: string;
    public name!: string;
    public tips!: string;
    public actions: Action[] = [];
    public sectionTitles!: TerminalSectionTitles;
    public infos!: TerminalInfos;
    public buttons!: TerminalButtons;
    public projects!: Project[];
    public items!: Item[];
    public rebelBases: RebelBase[] = [];

    public load(object: any): Terminal {
        if (object) {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.tips = object.tips;
            this.items = object.items;
            if (typeof object.actions !== 'undefined') {
                object.actions.forEach((actionObject: any) => {
                    const action = (new Action()).load(actionObject);
                    this.actions.push(action);
                });
            }
            this.sectionTitles = new TerminalSectionTitles().load(object.sectionTitles);
            this.infos = new TerminalInfos().load(object.infos);
            this.buttons = new TerminalButtons().load(object.buttons);
            this.projects = object.projects?.map((project: any) => new Project().load(project));
            this.rebelBases = object.rebelBases?.map((rebelBase: any) => new RebelBase().load(rebelBase));
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

    public getActionByKeyOrThrow(key: string): Action {
        const action = this.getActionByKey(key);
        if (!action) {
            throw new Error(`Action with key ${key} not found`);
        }
        return action;
    }
}
