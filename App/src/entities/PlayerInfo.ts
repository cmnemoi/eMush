import { User } from "./User";
import { CharacterConfig } from "./Config/CharacterConfig";

export class PlayerInfo {
    public id: number|null;
    public user: User|null;
    public gameStatus: string|null;
    public characterConfig: CharacterConfig|null;

    constructor() {
        this.id = null;
        this.user = null;
        this.gameStatus = null;
        this.characterConfig = null;
    }

    load(object : any): PlayerInfo {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.gameStatus = object.gameStatus;
            if (typeof object.user !== "undefined") {
                this.user = (new User()).load(object.user);
            }
            if (typeof object.characterConfig !== "undefined") {
                this.characterConfig = (new CharacterConfig()).load(object.characterConfig);
            }
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: string): PlayerInfo {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
