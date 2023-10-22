import { Planet } from "@/entities/Planet";
import { Explorator } from "./Explorator";

export class Exploration {
    public id!: number;
    public createdAt!: Date;
    public updatedAt!: Date;
    public planet!: Planet;
    public explorators!: Explorator[];
    public logs!: string[];

    public load(object: any): Exploration {
        if (object) {
            this.id = object.id;
            this.createdAt = object.createdAt;
            this.updatedAt = object.updatedAt;
            this.planet = (new Planet()).load(object.planet);
            this.explorators = object.explorators.map((explorator: any) => (new Explorator()).load(explorator));
            this.logs = object.logs;
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
}
    