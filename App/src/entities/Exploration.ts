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

export class Exploration {
    public createdAt!: Date;
    public updatedAt!: Date;
    public planet!: Planet;
    public explorators!: Explorator[];
    public logs!: ExplorationLogs[];
    public estimatedDuration!: number;
    public timer!: TimerCycle;
    public uiElements!: ExplorationUiElements;

    public load(object: any): Exploration {
        if (object) {
            this.createdAt = object.createdAt;
            this.updatedAt = object.updatedAt;
            this.planet = (new Planet()).load(object.planet);
            this.explorators = object.explorators.map((explorator: any) => (new Explorator()).load(explorator));
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
