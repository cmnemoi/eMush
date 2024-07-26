import { Daedalus } from "@/entities/Daedalus";
import { Room } from "@/entities/Room";
import { Item } from "@/entities/Item";
import { Status } from "@/entities/Status";
import { Action } from "@/entities/Action";
import { Character } from "@/entities/Character";
import { QuantityPoint } from "@/entities/QuantityPoint";
import { NameDescObject } from "@/entities/NameDescObject";
import { SpaceBattle } from "./SpaceBattle";
import { StatusPlayerNameEnum } from "@/enums/status.player.enum";
import { Terminal } from "@/entities/Terminal";
import { Exploration } from "@/entities/Exploration";
import { TerminalEnum } from "@/enums/terminal.enum";
import { SkillPoint } from "@/entities/SkillPoint";

export type Skill = {
    key: string;
    name: string;
    description: string;
};

export class Player {
    public id!: number;
    public gameStatus: string|null;
    public character!: Character;
    public actionPoint: QuantityPoint|null;
    public movementPoint: QuantityPoint|null;
    public healthPoint: QuantityPoint|null;
    public moralPoint: QuantityPoint|null;
    public triumph: QuantityPoint|null;
    public daedalus: Daedalus|null;
    public items: Array<Item>;
    public diseases: Array<Status>;
    public statuses: Array<Status>;
    public actions: Array<Action>;
    public room: Room|null;
    public spaceBattle: SpaceBattle|null;
    public terminal: Terminal|null;
    public titles: Array<NameDescObject>;
    public exploration: Exploration|null;
    public humanSkills: Array<Skill>;
    public mushSkills: Array<Skill>;
    public skillPoints: Array<SkillPoint>;
    public isSeated: boolean;
    public language: string;

    public constructor() {
        this.gameStatus = null;
        this.actionPoint = null;
        this.movementPoint = null;
        this.healthPoint = null;
        this.moralPoint = null;
        this.triumph = null;
        this.gameStatus = null;
        this.daedalus = null;
        this.items = [];
        this.statuses = [];
        this.diseases = [];
        this.actions = [];
        this.room = null;
        this.spaceBattle = null;
        this.terminal = null;
        this.titles = [];
        this.exploration = null;
        this.humanSkills = [];
        this.mushSkills = [];
        this.skillPoints = [];
        this.isSeated = false;
        this.language = '';
    }

    public load(object: any): Player {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.gameStatus = object.gameStatus;
            this.language = object.language;

            this.character = (new Character()).load(object.character);

            this.gameStatus = object.gameStatus;
            if (typeof object.actionPoint !== 'undefined') {
                this.actionPoint = (new QuantityPoint()).load(object.actionPoint);
            }
            if (typeof object.movementPoint !== 'undefined') {
                this.movementPoint = (new QuantityPoint()).load(object.movementPoint);
            }
            if (typeof object.healthPoint !== 'undefined') {
                this.healthPoint = (new QuantityPoint()).load(object.healthPoint);
            }
            if (typeof object.moralPoint !== 'undefined') {
                this.moralPoint = (new QuantityPoint()).load(object.moralPoint);
            }
            if (typeof object.triumph !== 'undefined') {
                this.triumph = (new QuantityPoint()).load(object.triumph);
            }
            if (typeof object.daedalus !== 'undefined') {
                this.daedalus = (new Daedalus()).load(object.daedalus);
            }
            if (typeof object.room !== 'undefined') {
                this.room = (new Room()).load(object.room);
            }
            if (object.spaceBattle !== null && typeof object.spaceBattle !== 'undefined') {
                this.spaceBattle = (new SpaceBattle()).load(object.spaceBattle);
            }
            if (object.terminal) {
                this.terminal = (new Terminal()).load(object.terminal);
            }
            if (typeof object.items !== 'undefined') {
                object.items.forEach((itemObject: any) => {
                    const item = (new Item).load(itemObject);
                    this.items.push(item);
                });
            }
            if (typeof object.actions !== 'undefined') {
                object.actions.forEach((actionObject: any) => {
                    const action = (new Action()).load(actionObject);
                    this.actions.push(action);
                });
            }
            if (typeof object.statuses !== 'undefined') {
                object.statuses.forEach((statusObject: any) => {
                    const status = (new Status()).load(statusObject);
                    this.statuses.push(status);
                });
            }
            if (typeof object.diseases !== 'undefined') {
                object.diseases.forEach((statusObject:any) => {
                    const status = (new Status()).load(statusObject);
                    this.diseases.push(status);
                });
            }
            if (object.titles) {
                object.titles.forEach((titleObject:any) => {
                    const title = (new NameDescObject()).load(titleObject);
                    this.titles.push(title);
                });
            }
            if (object.exploration) {
                this.exploration = (new Exploration()).load(object.exploration);
            }
            if (object.humanSkills) {
                object.humanSkills.forEach((skillObject: any) => {
                    this.humanSkills.push(skillObject);
                });
            }
            if (object.mushSkills) {
                object.mushSkills.forEach((skillObject: any) => {
                    this.mushSkills.push(skillObject);
                });
            }
            if (object.skillPoints) {
                object.skillPoints.forEach((skillPointObject: any) => {
                    const point = (new SkillPoint()).load(skillPointObject);
                    this.skillPoints.push(point);
                });
            }
        }

        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: string): Player {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }

    public canSeeSpaceBattle(): boolean
    {
        return this.spaceBattle !== null;
    }

    public isLyingDown(): { "key" : string, "id" : number } | null
    {
        for (let i= 0; i<this.statuses.length; i++) {
            const status = this.statuses[i];
            if (status.key === 'lying_down') {
                return status.target;
            }
        }

        return null;
    }

    public isInARoom(): boolean
    {
        return this.room?.type === 'room';
    }

    public isFocused(): boolean
    {
        return this.statuses.filter((status: Status) => {
            return status.key === StatusPlayerNameEnum.FOCUSED;
        }).length > 0;
    }

    public isFocusedOnTerminal(terminal: string): boolean {
        return this.statuses.filter((status: Status) => {
            return status.key === StatusPlayerNameEnum.FOCUSED && status.target?.key === terminal;
        }).length > 0;
    }

    public isFocusedOnProjectsTerminal(): boolean {
        return this.isFocusedOnTerminal(TerminalEnum.NERON_CORE_TERMINAL) || this.isFocusedOnTerminal(TerminalEnum.AUXILIARY_TERMINAL);
    }

    public isFocusedOnPilgredTerminal(): boolean {
        return this.isFocusedOnTerminal(TerminalEnum.PILGRED_TERMINAL);
    }

    public isExploring(): boolean {
        return this.exploration !== null;
    }

    public hasStatusByKey(key: string): boolean {
        return this.statuses.filter((status: Status) => {
            return status.key === key;
        }).length > 0;
    }

    public getPublicStatuses(): Array<Status> {
        return this.statuses.filter((status: Status) => {
            return !status.isPrivate;
        });
    }

    public getSkillByKey(key: string): Skill|null {
        const skill = this.skills.filter((skill: Skill) => {
            return skill.key === key;
        });

        if (skill.length === 0) {
            return null;
        }

        return skill[0];
    }

    public getSkillPointByKey(key: string): SkillPoint|null {
        const points = this.skillPoints.filter((point: SkillPoint) => {
            return point.key === key;
        });

        if (points.length === 0) {
            return null;
        }

        return points[0];
    }

    public getSkillPointChargeByKey(key: string): number | null {
        const skillPoint = this.getSkillPointByKey(key);
        if (!skillPoint || !skillPoint.charge) {
            return null;
        }

        return skillPoint.charge.quantity;
    }

    public isDead(): boolean {
        return this.gameStatus === 'finished' || this.gameStatus === 'closed';
    }

    public isAlive(): boolean {
        return !this.isDead();
    }

    public isMush(): boolean {
        return this.hasStatusByKey(StatusPlayerNameEnum.MUSH);
    }
}
