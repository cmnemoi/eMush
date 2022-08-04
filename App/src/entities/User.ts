import { UserRole } from "@/enums/user_role.enum";

export class User {
    public id : number|null
    public username : string|null
    public currentGame : number|null
    public roles : UserRole[]

    constructor() {
        this.id = null;
        this.username = null;
        this.currentGame = null;
        this.roles = [];
    }
    load(object: any): User {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.username = object.username;
            this.currentGame = object.currentGame;
            this.roles = object.roles;
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
            this.roles = object.roles;
        }

        return this;
    }
}
