export class User {
    public id : number|null
    public username : string|null
    public currentGame : number|null

    constructor() {
        this.id = null;
        this.username = null;
        this.currentGame = null;
    }
    load(object: User): User {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.username = object.username;
            this.currentGame = object.currentGame;
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: string): User {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.id = object.id;
            this.username = object.username;
            this.currentGame = object.currentGame;
        }

        return this;
    }
}
