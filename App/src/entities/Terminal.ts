import { Action } from "@/entities/Action";
import { TerminalSectionTitles } from "@/entities/TerminalSectionTitles";
import { TerminalInfos } from "@/entities/TerminalInfos";
import { TerminalButtons } from "@/entities/TerminalButtons";
import { Project } from "@/entities/Project";
import { Item } from "./Item";
import { RebelBase } from "@/entities/RebelBase";
import { XylophEntry } from "@/entities/XylophEntry";
import { Trade } from "@/entities/Trade";

type TerminalData = {
    id?: number;
    key?: string;
    name?: string;
    tips?: string;
    items?: Item[];
    actions?: Array<Action>;
    sectionTitles?: TerminalSectionTitles;
    infos?: TerminalInfos;
    buttons?: TerminalButtons;
    projects?: Array<Project>;
    rebelBases?: Array<RebelBase>;
    xylophEntries?: Array<XylophEntry>;
    trades?: Array<Trade>;
};

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
    public xylophEntries: XylophEntry[] = [];
    public trades: Trade[] = [];

    public load(object: TerminalData): Terminal {
        if (object) {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.tips = object.tips;
            this.items = object.items;
            if (typeof object.actions !== 'undefined') {
                object.actions.forEach((actionObject: Action) => {
                    const action = (new Action()).load(actionObject);
                    this.actions.push(action);
                });
            }
            this.sectionTitles = new TerminalSectionTitles().load(object.sectionTitles);
            this.infos = new TerminalInfos().load(object.infos);
            this.buttons = new TerminalButtons().load(object.buttons);
            this.projects = object.projects?.map((project: Project) => new Project().load(project));
            this.rebelBases = object.rebelBases?.map((rebelBase: RebelBase) => new RebelBase().load(rebelBase));
            this.xylophEntries = object.xylophEntries?.map((xylophEntry: XylophEntry) => new XylophEntry().load(xylophEntry));
            this.trades = object.trades?.map((trade: Trade) => new Trade().load(trade));
        }
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: string): Terminal {
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
