import { QuantityPoint } from "@/entities/QuantityPoint";
import { TimerCycle } from "@/entities/TimerCycle";
import { GameCalendar } from "@/entities/GameCalendar";
import { Planet } from "@/entities/Planet";
import { DaedalusExploration } from "./DaedalusExploration";
import { Minimap } from "@/entities/Minimap";
import { toArray } from "@/utils/toArray";

export type DaedalusProject = {
    type: string;
    translatedType: string;
    key: string;
    name: string;
    description: string;
    lore: string;
}

export type DaedalusProjects = {
    pilgred: DaedalusProject|undefined;
    neronProjects: DaedalusProject[];
    researchProjects: DaedalusProject[];
}

type DaedalusData = {
    id?: number;
    oxygen?: Parameters<QuantityPoint["load"]>[0];
    fuel?: Parameters<QuantityPoint["load"]>[0];
    hull?: Parameters<QuantityPoint["load"]>[0];
    shield?: Parameters<QuantityPoint["load"]>[0];
    timer?: Parameters<TimerCycle["load"]>[0];
    calendar?: Parameters<GameCalendar["load"]>[0];
    cryogenizedPlayers?: number;
    humanPlayerAlive?: number;
    humanPlayerDead?: number;
    mushPlayerAlive?: number;
    mushPlayerDead?: number;
    crewPlayer?: Parameters<QuantityPoint["load"]>[0];
    inOrbitPlanet?: Parameters<Planet["load"]>[0];
    isDaedalusTravelling?: boolean;
    attackingHunters?: number;
    // NB: assigned directly to `this.exploration` (typed DaedalusExploration|null) below without
    // calling DaedalusExploration.load(), unlike the sibling fields above (oxygen/fuel/hull/...) —
    // likely a pre-existing inconsistency, kept as-is to avoid changing runtime behavior.
    onGoingExploration?: Parameters<DaedalusExploration["load"]>[0];
    projects?: DaedalusProjects;
};

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
    public minimap: Minimap|null;
    public inOrbitPlanet: Planet|null;
    public isDaedalusTravelling: boolean;
    public attackingHunters: number;
    public exploration: DaedalusExploration|null;
    public projects!: DaedalusProjects;

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
        this.attackingHunters = 0;
        this.exploration = null;
    }
    load(object : DaedalusData): Daedalus {
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
            if (object.shield) {
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
            this.attackingHunters = object.attackingHunters;
            this.exploration = object.onGoingExploration;
            this.projects = object.projects;
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

    hasActivePlasmaShield(): boolean {
        const neronProjects = toArray(this.projects.neronProjects);
        return neronProjects.map(project => project.key).includes('plasma_shield') && (this.shield?.quantity ?? 0) > 0;
    }

    shieldIsBroken(): boolean {
        const neronProjects = toArray(this.projects.neronProjects);
        return neronProjects.map(project => project.key).includes('plasma_shield') && this.shield?.quantity === 0;
    }
}
