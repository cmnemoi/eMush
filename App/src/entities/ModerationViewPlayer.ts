import { Character } from "./Character";
import { PlayerVariables } from "./PlayerVariables";
import { Status } from "@/entities/Status";

interface ShortUser {
    id: number;
    userId: string;
    username: string;
    isBanned: boolean;
}

export class ModerationViewPlayer {
    public id!: number;
    public user!: ShortUser;
    public character!: Character;
    public playerVariables!: PlayerVariables;
    public isMush!: boolean;
    public statuses: Array<Status>;
    public diseases: Array<Status>;
    public currentRoom: string|null;
    public isAlive!: boolean;

    public constructor() {
        this.statuses = [];
        this.diseases = [];
        this.currentRoom = null;
    }

    public load(object: any): ModerationViewPlayer {
        if (object) {
            this.id = object.id;
            this.user = object.user;
            this.character = new Character().load(object.character);
            this.playerVariables = new PlayerVariables().load(object.playerVariables);
            this.isMush = object.isMush;
            this.currentRoom = object.currentRoom;
            this.statuses = object.statuses?.map((statusObject: any) => { return (new Status()).load(statusObject); });
            this.diseases = object.diseases?.map((statusObject: any) => { return (new Status()).load(statusObject); });
            this.isAlive = object.isAlive;
        }
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this, null, 4);
    }

    public decode(jsonString: string): ModerationViewPlayer {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}