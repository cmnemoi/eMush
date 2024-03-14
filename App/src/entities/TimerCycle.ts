export class TimerCycle {

    public name: string|null;
    public description: string|null;
    public timerCycle: Date|null;

    constructor() {
        this.timerCycle = null;
        this.name = null;
        this.description =null;
    }
    load(object :any): TimerCycle {
        if (object) {
            this.timerCycle = object.timerCycle ? new Date(object.timerCycle) : null;
            this.name = object.name;
            this.description = object.description;
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString : string): TimerCycle {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.timerCycle = object.timerCycle;
            this.name = object.name;
            this.description = object.description;
        }

        return this;
    }
}
