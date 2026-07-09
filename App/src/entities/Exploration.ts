import { Planet } from "@/entities/Planet";
import { Explorator } from "@/entities/Explorator";
import { ExplorationLogs } from "@/entities/ExplorationLogs";
import { TimerCycle } from "./TimerCycle";

type ExplorationUiElements = {
    tips: string;
    recoltedInfos: string;
    newStep: string;
    lost: string;
    finished: string;
};

type ExplorationData = {
    createdAt?: Date;
    updatedAt?: Date;
    planet?: Parameters<Planet["load"]>[0];
    explorators: Array<Parameters<Explorator["load"]>[0]>;
    logs?: ExplorationLogs[];
    // NB: matches the field actually read below (estimated_duration, snake_case) — likely a
    // pre-existing mismatch vs. the class's camelCase estimatedDuration field.
    estimated_duration?: number;
    timer?: Parameters<TimerCycle["load"]>[0];
    uiElements?: ExplorationUiElements;
};

export class Exploration {
    public createdAt!: Date;
    public updatedAt!: Date;
    public startDay!: number;
    public startCycle!: number;
    public planet!: Planet;
    public explorators!: Explorator[];
    public logs!: ExplorationLogs[];
    public estimatedDuration!: number;
    public timer!: TimerCycle;
    public uiElements!: ExplorationUiElements;

    public load(object: ExplorationData): Exploration {
        if (object) {
            this.createdAt = object.createdAt;
            this.updatedAt = object.updatedAt;
            this.startDay = object.startDay;
            this.startCycle = object.startCycle;
            this.planet = (new Planet()).load(object.planet);
            this.explorators = object.explorators.map((explorator: Parameters<Explorator["load"]>[0]) => (new Explorator()).load(explorator));
            this.logs = object.logs;
            this.estimatedDuration = object.estimated_duration;
            this.timer = (new TimerCycle()).load(object.timer);
            this.uiElements = object.uiElements;
        }

        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString : string): Exploration {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }

    public getPlanetName(): string | null {
        return this.planet.name;
    }

    public getExploratorNames(): string[] {
        return this.explorators.map((explorator: Explorator) => explorator.name);
    }
}
