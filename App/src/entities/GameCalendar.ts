export class GameCalendar {
    public name: string|null;
    public description: string|null;
    public day: number|null;
    public dayName: string|null;
    public cycle: number|null;
    public cycleName: string|null;


    constructor() {
        this.day = null;
        this.cycle = null;
        this.name = null;
        this.description = null;
        this.dayName = null;
        this.cycleName = null;
    }
    load(object :any): GameCalendar {
        if (typeof object !== "undefined") {
            this.day = object.day;
            this.cycle = object.cycle;
            this.name = object.name;
            this.description = object.description;
            this.dayName = object.dayName;
            this.cycleName = object.cycleName;
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString : string): GameCalendar {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.day = object.day;
            this.cycle = object.cycle;
            this.name = object.name;
            this.description = object.description;
            this.dayName = object.dayName;
            this.cycleName = object.cycleName;
        }

        return this;
    }
}
