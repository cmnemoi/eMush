export class Daedalus {
    public id: number|null;
    public day: number|null;
    public cycle: number|null;
    public oxygen: number|null;
    public fuel: number|null;
    public hull: number|null;
    public shield: number|null;
    public currentCycle: number|null;
    public nextCycle: Date|null;
    public cryogenizedPlayers: number;
    public humanPlayerAlive: number;
    public humanPlayerDead: number;
    public mushPlayerAlive: number;
    public mushPlayerDead: number;
    public crewPlayer: number;

    constructor() {
        this.id = null;
        this.day = null;
        this.cycle = null;
        this.oxygen = null;
        this.fuel = null;
        this.hull = null;
        this.shield = null;
        this.currentCycle = null;
        this.nextCycle = null;
        this.cryogenizedPlayers = 0;
        this.humanPlayerAlive = 0;
        this.humanPlayerDead = 0;
        this.mushPlayerAlive = 0;
        this.mushPlayerDead = 0;
        this.crewPlayer = 0;
    }
    load(object :any): Daedalus {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.day = object.day;
            this.cycle = object.cycle;
            this.oxygen = object.oxygen;
            this.fuel = object.fuel;
            this.hull = object.hull;
            this.shield = object.shield;
            this.currentCycle = object.currentCycle;
            this.nextCycle = new Date(object.nextCycle);
            this.cryogenizedPlayers = object.cryogenizedPlayers;
            this.humanPlayerAlive = object.humanPlayerAlive;
            this.humanPlayerDead = object.humanPlayerDead;
            this.mushPlayerAlive = object.mushPlayerAlive;
            this.mushPlayerDead = object.mushPlayerDead;
            this.crewPlayer = object.crewPlayer;
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString : string): Daedalus {
        if (jsonString) {
            let object = JSON.parse(jsonString);
            this.id = object.id;
            this.day = object.day;
            this.cycle = object.cycle;
            this.oxygen = object.oxygen;
            this.fuel = object.fuel;
            this.hull = object.hull;
            this.shield = object.shield;
        }

        return this;
    }
}
