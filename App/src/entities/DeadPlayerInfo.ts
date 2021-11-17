import { Character } from "@/entities/Character";

export class DeadPlayerInfo {
    public id: number|null;
    public character!: Character;
    public endCauseKey: string|null;
    public endCauseValue: string|null;
    public endCauseDescription: string|null;
    public players: Array<DeadPlayerInfo>;


    constructor() {
        this.id = null;
        this.character = new Character();
        this.endCauseKey = null;
        this.endCauseValue = null;
        this.endCauseDescription= null;
        this.players = [];
    }

    load(object : any): DeadPlayerInfo {
        if (typeof object !== "undefined") {
            this.id = object.id;

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
