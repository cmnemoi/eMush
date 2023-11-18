export class PlanetSector {
    public id!: number;
    public key!: string;
    public name!: string;
    public description!: string;
    public isVisited!: boolean;

    public load(object: any): PlanetSector {
        if (object) {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.description = object.description;
            this.isVisited = object.isVisited;
        }
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: any): PlanetSector {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
