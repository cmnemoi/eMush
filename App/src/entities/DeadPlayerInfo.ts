export class DeadPlayerInfo {
    public id: number|null;
    public characterKey: string|null;
    public characterValue: string|null;
    public endCauseKey: string|null;
    public endCauseValue: string|null;
    public endCauseDescription: string|null;
    public players: Array<DeadPlayerInfo>;


    constructor() {
        this.id = null;
        this.characterKey = null;
        this.characterValue = null;
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
                this.characterKey = object.character['key'];
                this.characterValue = object.character['value'];
            }

            if (typeof object.players !== 'undefined') {
                object.players.forEach((deadPlayerObject: any) => {
                    let deadPlayer = (new DeadPlayerInfo()).load(deadPlayerObject);
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
            let object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
