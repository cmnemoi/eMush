export class User {
    public id : number|null
    public username : string|null
    public currentGame : number|null

    constructor() {
        this.id = null;
        this.username = null;
        this.currentGame = null;
    }
    load(object: any) {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.username = object.username;
            this.currentGame = object.currentGame;
        }
        return this;
    }
    jsonEncode() {
        return JSON.stringify(this);
    }
    decode(jsonString: string) {
        if (jsonString) {
            let object = JSON.parse(jsonString);
            this.id = object.id;
            this.username = object.username;
            this.currentGame = object.currentGame;
        }

        return this;
    }
}
