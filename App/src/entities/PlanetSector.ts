export class PlanetSector {
    public id!: number;
    public updatedAt!: string | null;
    public key!: string;
    public name!: string;
    public description!: string;
    public isVisited!: boolean;
    public isRevealed!: boolean;
    public isNextSector!: boolean | undefined;

    public load(object: any): PlanetSector {
        if (object) {
            this.id = object.id;
            this.updatedAt = object.updatedAt;
            this.key = object.key;
            this.name = object.name;
            this.description = object.description;
            this.isVisited = object.isVisited;
            this.isRevealed = object.isRevealed;
            this.isNextSector = object.isNextSector;
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
