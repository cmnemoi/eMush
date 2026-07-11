import { Character } from "@/entities/Character";
import { toArray } from "@/utils/toArray";

type DeadPlayerInfoData = {
    id?: number;
    deathDay?: integer;
    deathCycle?: integer;
    likes?: integer;
    endCause?: { key?: string; name?: string; shortName?: string; description?: string };
    character?: { key?: string; value?: string };
    players?: Array<Parameters<DeadPlayerInfo["load"]>[0]>;
    triumphGains?: Array<string> | Record<string, string>;
    playerHighlights?: string[];
};

export class DeadPlayerInfo {
    public id!: number;
    public character!: Character;
    public deathDay: integer|null;
    public deathCycle: integer|null;
    public endCauseKey!: string;
    public endCauseShortName!: string;
    public endCauseName!: string;
    public endCauseDescription!: string;
    public likes: integer;
    public triumphGains: string[] = [];
    public playerHighlights: string[] = [];
    public players: Array<DeadPlayerInfo>;

    constructor() {
        this.character = new Character();
        this.deathDay = null;
        this.deathCycle = null;
        this.likes = 0;
        this.players = [];
    }

    load(object : DeadPlayerInfoData): DeadPlayerInfo {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.deathDay = object.deathDay;
            this.deathCycle = object.deathCycle;
            this.likes = object.likes ?? 0;

            if (typeof object.endCause !== "undefined") {
                this.endCauseKey = object.endCause['key'];
                this.endCauseName = object.endCause['name'];
                this.endCauseShortName = object.endCause['shortName'];
                this.endCauseDescription = object.endCause['description'];
            }

            if (typeof object.character !== "undefined") {
                this.character.key = object.character['key'];
                this.character.name = object.character['value'];
            }

            if (typeof object.players !== 'undefined') {
                object.players.forEach((deadPlayerObject: Parameters<DeadPlayerInfo["load"]>[0]) => {
                    const deadPlayer = (new DeadPlayerInfo()).load(deadPlayerObject);
                    this.players.push(deadPlayer);
                });
            }

            if (object.triumphGains) {
                toArray(object.triumphGains).forEach((triumphGainObject: string) => {
                    this.triumphGains.push(triumphGainObject);
                });
            }

            this.playerHighlights = object.playerHighlights;
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: string): DeadPlayerInfo {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }

    hasGoodEndCause(): boolean {
        return ['sol_return', 'eden'].includes(this.endCauseKey);
    }
}
