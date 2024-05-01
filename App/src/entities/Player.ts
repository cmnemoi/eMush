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
import { SpecialistPoint } from "@/entities/SpecialistPoint";

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
    public skills: Array<Status>;
    public shootPoint: QuantityPoint|null;
    public specialistPoints: Array<SpecialistPoint>;
    public isSeated: boolean;

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
        this.skills = [];
        this.shootPoint = null;
        this.specialistPoints = [];
        this.isSeated = false;
    }

    public load(object: any): Player {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.gameStatus = object.gameStatus;

            this.character = new Character();
            this.character.key = object.character['key'];
            this.character.name = object.character['value'];
            this.character.description = object.character['description'];

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
            if (object.skills) {
                object.skills.forEach((skillObject: any) => {
                    const skill = (new Status()).load(skillObject);
                    this.skills.push(skill);
                });
            }
            if (object.shootPoint) {
                this.shootPoint = (new QuantityPoint()).load(object.shootPoint);
            }
            if (object.specialistPoints) {
                object.specialistPoints.forEach((specialistPointObject: any) => {
                    const point = (new SpecialistPoint()).load(specialistPointObject);
                    this.specialistPoints.push(point);
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

    public getSkillByKey(key: string): Status|null {
        const skill = this.skills.filter((skill: Status) => {
            return skill.key === key;
        });

        if (skill.length === 0) {
            return null;
        }

        return skill[0];
    }

    public getSkillPointsByKey(key: string): number | null {
        const skill = this.getSkillByKey(key);
        if (!skill) {
            return null;
        }

        return skill.charge;
    }

    public getSpecialistPointByKey(key: string): SpecialistPoint|null {
        const points = this.specialistPoints.filter((point: SpecialistPoint) => {
            return point.name === key;
        });

        if (points.length === 0) {
            return null;
        }

        return points[0];
    }

    public getSpecialistPointChargeByKey(key: string): number | null {
        const specialistPoint = this.getSpecialistPointByKey(key);
        if (!specialistPoint || !specialistPoint.charge) {
            return null;
        }

        return specialistPoint.charge.quantity;
    }

    public isDead(): boolean {
        return this.gameStatus === 'finished' || this.gameStatus === 'closed';
    }

    public isAlive(): boolean {
        return !this.isDead();
    }
}
