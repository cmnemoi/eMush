import { ExplorationLogs } from "@/entities/ExplorationLogs";
import { ClosedExplorator } from "./ClosedExplorator";

export class ClosedExploration {
    public id!: number;
    public iri!: string;
    public createdAt!: Date;
    public updatedAt!: Date;
    public planet!: string;
    public explorators!: ClosedExplorator[];
    public sectors!: string[];
    public logs!: ExplorationLogs[];
    public tips!: string;

    public load(object: any): ClosedExploration {
        if (object) {
            this.id = object.id;
            this.iri = object['@id'];
            this.createdAt = object.createdAt;
            this.updatedAt = object.updatedAt;
            this.planet = object.planetName;
            this.explorators = object.closedExplorators.map((player: any) => new ClosedExplorator().load(player));
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
