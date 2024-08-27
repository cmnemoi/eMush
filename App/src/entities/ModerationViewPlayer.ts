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
    public daedalusId: number|null;
    public user!: ShortUser;
    public character!: {characterName: string, characterValue: string};
    public isMush!: boolean;
    public isAlive!: boolean;
    public cycleStartedAt: Date|null;
    public daedalusDay!: number;
    public daedalusCycle!: number;

    public constructor() {
        this.daedalusId = null;
        this.cycleStartedAt = null;
    }

    public load(object: any): ModerationViewPlayer {
        if (object) {
            this.id = object.id;
            this.daedalusId = object.daedalusId;
            this.user = object.user;
            this.character = object.character;
            this.isMush = object.isMush;
            this.isAlive = object.isAlive;
            this.cycleStartedAt = object.cycleStartedAt ? new Date(object.cycleStartedAt) : null;
            this.daedalusDay = object.daedalusDay;
            this.daedalusCycle = object.daedalusCycle;
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
