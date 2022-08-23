import { QuantityPoint } from "@/entities/QuantityPoint";

export class Daedalus {
    public id: number|null;
    public day: number|null;
    public cycle: number|null;
    public oxygen: QuantityPoint|null;
    public fuel: QuantityPoint|null;
    public hull: QuantityPoint|null;
    public shield: QuantityPoint|null;
    public currentCycle: QuantityPoint|null;
    public nextCycle: Date|null;
    public cryogenizedPlayers: number;
    public humanPlayerAlive: number;
    public humanPlayerDead: number;
    public mushPlayerAlive: number;
    public mushPlayerDead: number;
    public crewPlayer: QuantityPoint | null;

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
        this.crewPlayer = null;
    }
    load(object :any): Daedalus {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.day = object.day;
            this.cycle = object.cycle;
            if (typeof object.oxygen !== 'undefined') {
                this.oxygen = (new QuantityPoint()).load(object.oxygen);
            }
            if (typeof object.fuel !== 'undefined') {
                this.fuel = (new QuantityPoint()).load(object.fuel);
            }
            if (typeof object.hull !== 'undefined') {
                this.hull = (new QuantityPoint()).load(object.hull);
            }
            if (typeof object.shield !== 'undefined') {
                this.shield = (new QuantityPoint()).load(object.shield);
            }
            if (typeof object.currentCycle !== 'undefined') {
                this.currentCycle = (new QuantityPoint()).load(object.currentCycle);
            }
            this.nextCycle = new Date(object.nextCycle);
            this.cryogenizedPlayers = object.cryogenizedPlayers;
            this.humanPlayerAlive = object.humanPlayerAlive;
            this.humanPlayerDead = object.humanPlayerDead;
            this.mushPlayerAlive = object.mushPlayerAlive;
            this.mushPlayerDead = object.mushPlayerDead;
            if (typeof object.crewPlayer !== 'undefined') {
                this.crewPlayer = (new QuantityPoint()).load(object.crewPlayer);
            };
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString : string): Daedalus {
        if (jsonString) {
            const object = JSON.parse(jsonString);
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
