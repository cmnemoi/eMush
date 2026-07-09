import { ExplorationLogs } from "@/entities/ExplorationLogs";
import { ClosedExplorator, ClosedExploratorData } from "./ClosedExplorator";

type ClosedExplorationData = {
    id?: number;
    "@id"?: string;
    createdAt?: Date;
    updatedAt?: Date;
    planetName?: string;
    closedExplorators?: ClosedExploratorData[];
    exploredSectorKeys?: string[];
    logs?: ExplorationLogs[];
    tips?: string;
};

export class ClosedExploration {
    public id!: number;
    public uuid!: string;
    public iri!: string;
    public createdAt!: Date;
    public updatedAt!: Date;
    public startDay!: number;
    public startCycle!: number;
    public planet!: string;
    public explorators!: ClosedExplorator[];
    public sectors!: string[];
    public logs!: ExplorationLogs[];
    public tips!: string;

    public load(object: ClosedExplorationData): ClosedExploration {
        if (object) {
            this.id = object.id;
            this.uuid = object.uuid;
            this.iri = object['@id'];
            this.createdAt = object.createdAt;
            this.updatedAt = object.updatedAt;
            this.startDay = object.startDay;
            this.startCycle = object.startCycle;
            this.planet = object.planetName;
            this.explorators = object.closedExplorators.map((player) => new ClosedExplorator().load(player));
            this.sectors = object.exploredSectorKeys;
            this.logs = object.logs;
            this.tips = object.tips;
        }

        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this, null, 4);
    }

    public decode(jsonString : string): ClosedExploration {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
