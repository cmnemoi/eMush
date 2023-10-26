import { QuantityPoint } from "@/entities/QuantityPoint";
import { TimerCycle } from "@/entities/TimerCycle";
import { GameCalendar } from "@/entities/GameCalendar";
import { Planet } from "@/entities/Planet";

export class Daedalus {
    public id: number|null;
    public oxygen: QuantityPoint|null;
    public fuel: QuantityPoint|null;
    public hull: QuantityPoint|null;
    public shield: QuantityPoint|null;
    public timer: TimerCycle|null;
    public calendar: GameCalendar|null;
    public cryogenizedPlayers: number;
    public humanPlayerAlive: number;
    public humanPlayerDead: number;
    public mushPlayerAlive: number;
    public mushPlayerDead: number;
    public crewPlayer: QuantityPoint | null;
    public minimap: any;
    public inOrbitPlanet: Planet|null;
    public isDaedalusTravelling: boolean;

    constructor() {
        this.id = null;
        this.oxygen = null;
        this.fuel = null;
        this.hull = null;
        this.shield = null;
        this.timer = null;
        this.calendar = null;
        this.cryogenizedPlayers = 0;
        this.humanPlayerAlive = 0;
        this.humanPlayerDead = 0;
        this.mushPlayerAlive = 0;
        this.mushPlayerDead = 0;
        this.crewPlayer = null;
        this.minimap = null;
        this.inOrbitPlanet = null;
        this.isDaedalusTravelling = false;
    }
    load(object :any): Daedalus {
        if (typeof object !== "undefined") {
            this.id = object.id;
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
            if (typeof object.timer !== 'undefined') {
                this.timer = (new TimerCycle()).load(object.timer);
            }
            if (typeof object.calendar !== 'undefined') {
                this.calendar = (new GameCalendar()).load(object.calendar);
            }
            this.cryogenizedPlayers = object.cryogenizedPlayers;
            this.humanPlayerAlive = object.humanPlayerAlive;
            this.humanPlayerDead = object.humanPlayerDead;
            this.mushPlayerAlive = object.mushPlayerAlive;
            this.mushPlayerDead = object.mushPlayerDead;
            if (typeof object.crewPlayer !== 'undefined') {
                this.crewPlayer = (new QuantityPoint()).load(object.crewPlayer);
            }
            if (object.inOrbitPlanet) {
                this.inOrbitPlanet = (new Planet()).load(object.inOrbitPlanet);
            }
            this.isDaedalusTravelling = object.isDaedalusTravelling;
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString : string): Daedalus {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }


}
