import { is_granted, UserRole } from "@/enums/user_role.enum";

export class User {
    public id : number|null;
    public userId : string|null;
    public username : string|null;
    public currentGame : number|null;
    public roles : UserRole[];

    constructor() {
        this.id = null;
        this.userId = null;
        this.username = null;
        this.currentGame = null;
        this.roles = [];
    }
    load(object: any): User {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.userId = object.userId;
            this.username = object.username;
            this.currentGame = object.currentGame ? object.currentGame.id : null;
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
            this.userId = object.userId;
            this.username = object.username;
            this.currentGame = object.currentGame ?? null;
            this.roles = object.roles;
        }

        return this;
    }
    isAdmin(): boolean {
        return is_granted(UserRole.ADMIN, this);
    }
}
