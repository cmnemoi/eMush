import { Character } from "./Character";
import { PlayerVariables } from "./PlayerVariables";
import { User } from "./User";

export class AdminViewPlayer {
    public id!: number;
    public user!: User;
    public character!: Character;
    public playerVariables!: PlayerVariables;
    public isMush!: boolean;

    public load(object: any): AdminViewPlayer {
        if (object !== undefined && object !== null) {
            this.id = object.id;
            this.user = new User().load(object.user);
            this.character = new Character().load(object.character);
            this.playerVariables = new PlayerVariables().load(object.playerVariables);
            this.isMush = object.isMush;
        }
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: string): AdminViewPlayer {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}