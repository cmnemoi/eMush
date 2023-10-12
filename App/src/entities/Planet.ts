import { PlanetSector } from "./PlanetSector";

export class Planet {
    public id!: number;
    public name!: string;
    public orientation!: string;
    public distance!: number;
    public sectors!: PlanetSector[];

    public load(object: any): Planet {
        if (object) {
            this.id = object.id;
            this.name = object.name;
            this.orientation = object.orientation;
            this.distance = object.distance;
            this.sectors = object.sectors;
        }
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: any): Planet {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}