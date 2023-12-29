import { Character } from "./Character";
import { PlayerVariables } from "./PlayerVariables";
import { Status } from "@/entities/Status";
import { User } from "./User";

export class AdminViewPlayer {
    public id!: number;
    public user!: User;
    public character!: Character;
    public playerVariables!: PlayerVariables;
    public isMush!: boolean;
    public statuses: Array<Status>;
    public diseases: Array<Status>;
    public currentRoom: string|null;

    public constructor() {
        this.statuses = [];
        this.diseases = [];
        this.currentRoom = null;
    }

    public load(object: any): AdminViewPlayer {
        if (object !== undefined && object !== null) {
            this.id = object.id;
            this.user = new User().load(object.user);
            this.character = new Character().load(object.character);
            this.playerVariables = new PlayerVariables().load(object.playerVariables);
            this.isMush = object.isMush;
            this.currentRoom = object.currentRoom;
            if (object.statuses) {
                object.statuses.forEach((statusObject: any) => {
                    const status = (new Status()).load(statusObject);
                    this.statuses.push(status);
                });
            }
            if (object.diseases) {
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

    public decode(jsonString: string): AdminViewPlayer {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}