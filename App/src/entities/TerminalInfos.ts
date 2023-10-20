import { AdvanceDaedalusStatus } from "@/entities/AdvanceDaedalusStatus";
import { Planet } from "./Planet";

export class TerminalInfos {
    public difficulty: string|null;
    public advanceDaedalusStatus: AdvanceDaedalusStatus|null;
    public daedalusOrientation: string|null;
    public planets: Planet[]|null;
    public maxDiscoverablePlanets: number|null;
    public inOrbit: string|null;

    constructor() {
        this.difficulty = null;
        this.advanceDaedalusStatus = null;
        this.daedalusOrientation = null;
        this.planets = null;
        this.maxDiscoverablePlanets = null;
        this.inOrbit = null;
    }

    public load(object: any): TerminalInfos {
        if (object) {
            this.difficulty = object.difficulty;
            this.advanceDaedalusStatus = new AdvanceDaedalusStatus().load(object.advanceDaedalusStatus);
            this.daedalusOrientation = object.orientation;
            this.planets = object.planets?.map((planet: any) => new Planet().load(planet));
            this.maxDiscoverablePlanets = object.maxDiscoverablePlanets;
            this.inOrbit = object.inOrbit;
        }
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: any): TerminalInfos {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }

}