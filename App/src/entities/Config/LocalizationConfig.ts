import { GameConfig } from "@/entities/Config/GameConfig";

export class DifficultyConfig {
    public cyclePerGameDay: number|null;
    public cycleLength: number|null;
    public timeZone: string|null;
    public language: string|null;

    constructor() {
        this.cyclePerGameDay = null;
        this.cycleLength = null;
        this.timeZone = null;
        this.language = null;
    }
    load(object:any) : DifficultyConfig {
        if (typeof object !== "undefined") {
            this.cyclePerGameDay = object.cyclePerGameDay;
            this.cycleLength = object.cycleLength;
            this.timeZone = object.timeZone;
            this.language = object.language;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'cyclePerGameDay': this.cyclePerGameDay,
            'cycleLength': this.cycleLength,
            'timeZone': this.timeZone,
            'language': this.language,
        };
    }
    decode(jsonString : string): DifficultyConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.cyclePerGameDay = object.cyclePerGameDay;
            this.cycleLength = object.cycleLength;
            this.timeZone = object.timeZone;
            this.language = object.language;
        }

        return this;
    }
}
