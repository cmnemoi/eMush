export class ClosedPlayer {
    public iri: string|null;
    public id: number|null;
    public message: string|null;
    public endCause: string|null;
    public dayDeath: integer|null;
    public startCycle: integer|null;
    public cycleDeath: integer|null;
    public cyclesSurvived: integer|null;
    public likes: integer|null;
    public isMush: boolean|null;
    public characterKey: string|null;
    public username: string|null;
    public userId: string|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.message = null;
        this.endCause = null;
        this.dayDeath = null;
        this.startCycle = null;
        this.cycleDeath = null;
        this.cyclesSurvived = null;
        this.likes = null;
        this.isMush = null;
        this.characterKey = null;
        this.username = null;
        this.userId = null;
    }
    load(object :any): ClosedPlayer {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.message = object.message;
            this.endCause = object.endCause;
            this.dayDeath = object.dayDeath;
            this.startCycle = object.startCycle;
            this.cycleDeath = object.cycleDeath;
            this.likes = object.likes;
            this.isMush = object.isMush;
            this.characterKey = object.characterKey;
            this.username = object.username;
            this.userId = object.userId;
        }
        if (this.dayDeath && this.cycleDeath && this.startCycle){
            this.cyclesSurvived = (this.dayDeath - 1) * 8 + this.cycleDeath - this.startCycle;
        }

        return this;
    }
    jsonEncode(): object {
        const data : any = {
            'id': this.id,
            'message': this.message,
            'endCause': this.endCause,
            'dayDeath': this.dayDeath,
            'cycleDeath': this.cycleDeath,
            'startCycle': this.startCycle,
            'cyclesSurvived': this.cyclesSurvived,
            'likes': this.likes,
            'isMush': this.isMush,
            'characterKey': this.characterKey,
            'username': this.username,
            'userId': this.userId
        };

        return data;
    }
    decode(jsonString : string): ClosedPlayer {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }


}
