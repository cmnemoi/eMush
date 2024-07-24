import { User } from "./User";
import { CharacterConfig } from "./Config/CharacterConfig";

export class PlayerInfo {
    public id: number|null;
    public user: User|null;
    public gameStatus: string|null;
    public characterConfig: CharacterConfig|null;
    public daedalusId: number|null;

    constructor() {
        this.id = null;
        this.user = null;
        this.gameStatus = null;
        this.characterConfig = null;
        this.daedalusId = null;
    }

    load(object : any): PlayerInfo {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.gameStatus = object.gameStatus;
            if (typeof object.user !== "undefined") {
                this.user = (new User()).load(object.user);
            }
            if (typeof object.character !== "undefined") {
                this.characterConfig = (new CharacterConfig()).load(object.character);
                console.log(object.character);
                console.log(this.characterConfig);
            }
            this.daedalusId = object.daedalusId;
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
