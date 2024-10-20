import { is_granted, UserRole } from "@/enums/user_role.enum";

export class User {
    public id : number|null;
    public userId : string|null;
    public username : string|null;
    public playerInfo : number|null;
    public roles : UserRole[];
    public hasAcceptedRules! : boolean;

    constructor() {
        this.id = null;
        this.userId = null;
        this.username = null;
        this.playerInfo = null;
        this.roles = [];
    }
    load(object: any): User {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.userId = object.userId;
            this.username = object.username;
            this.playerInfo = object.playerInfo ? object.playerInfo : null;
            this.roles = object.roles;
            this.hasAcceptedRules = object.hasAcceptedRules;
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: string): User {
        if (jsonString) {
            return (new User()).load(JSON.parse(jsonString));
        }

        return this;
    }
    isAdmin(): boolean {
        return is_granted(UserRole.ADMIN, this);
    }
    isModerator(): boolean {
        return is_granted(UserRole.MODERATOR, this);
    }
}
