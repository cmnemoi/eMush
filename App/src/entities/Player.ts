import { Daedalus } from "@/entities/Daedalus";
import { Room } from "@/entities/Room";
import { Item } from "@/entities/Item";
import { Status } from "@/entities/Status";
import { Action } from "@/entities/Action";
import { Character } from "@/entities/Character";
import { QuantityPoint } from "@/entities/QuantityPoint";
import { SpaceBattle } from "./SpaceBattle";
import { StatusPlayerNameEnum } from "@/enums/status.player.enum";
import { TerminalEnum } from "@/enums/terminal.enum";

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
    }

    public load(object: any): Player {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.gameStatus = object.gameStatus;

            this.character = new Character();
            this.character.key = object.character['key'];
            this.character.name = object.character['value'];
            this.character.description = object.character['description'];
            this.character.skills = object.character['skills'];

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
        for (let i=0; i<this.statuses.length; i++) {
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

    // @TODO: in the future, translate and normalize directly terminal name from back end.
    public getFocusedTerminalName(): String|undefined|null {
        const status = this.statuses.filter((status: Status) => {
            return status.key === StatusPlayerNameEnum.FOCUSED;
        });

        if (status.length > 0 && Object.values(TerminalEnum).includes(status[0].target?.key as TerminalEnum)) {
            return status[0].target?.key;
        }

        return null;
    }
}
