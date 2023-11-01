import { Character } from "@/entities/Character";

export class DeadPlayerInfo {
    public id!: number;
    public character!: Character;
    public deathDay: integer|null;
    public deathCycle: integer|null;
    public endCauseKey: string|null;
    public endCauseValue: string|null;
    public endCauseDescription: string|null;
    public likes: integer;
    public players: Array<DeadPlayerInfo>;


    constructor() {
        this.character = new Character();
        this.deathDay = null;
        this.deathCycle = null;
        this.endCauseKey = null;
        this.endCauseValue = null;
        this.endCauseDescription= null;
        this.likes = 0;
        this.players = [];
    }

    load(object : any): DeadPlayerInfo {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.deathDay = object.deathDay;
            this.deathCycle = object.deathCycle;
            this.likes = object.likes ?? 0;

            if (typeof object.endCause !== "undefined") {
                this.endCauseKey = object.endCause['key'];
                this.endCauseValue = object.endCause['name'];
                this.endCauseDescription = object.endCause['description'];

            }

            if (typeof object.character !== "undefined") {
                this.character.key = object.character['key'];
                this.character.name = object.character['value'];
            }

            if (typeof object.players !== 'undefined') {
                object.players.forEach((deadPlayerObject: any) => {
                    const deadPlayer = (new DeadPlayerInfo()).load(deadPlayerObject);
                    this.players.push(deadPlayer);
                });
            }
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
}
