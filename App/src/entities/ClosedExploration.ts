import { ExplorationLogs } from "@/entities/ExplorationLogs";

export class ClosedExploration {
    public id!: number;
    public iri!: string;
    public createdAt!: Date;
    public updatedAt!: Date;
    public planet!: string;
    public explorators!: string[];
    public sectors!: string[];
    public logs!: ExplorationLogs[];

    public load(object: any): ClosedExploration {
        if (object) {
            this.id = object.id;
            this.iri = object['@id'];
            this.createdAt = object.createdAt;
            this.updatedAt = object.updatedAt;
            this.planet = object.planetName;
            this.explorators = object.exploratorNames;
            this.sectors = object.exploredSectorKeys;
            this.logs = object.logs;
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

    public getPlanetName(): string {
        return this.planet;
    }

    public getExploratorNames(): string[] {
        return this.explorators;
    }
}
    