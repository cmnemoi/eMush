import { Action } from "@/entities/Action";
import { PlanetSector } from "@/entities/PlanetSector";

export class Planet {
    private readonly numberOfPlanetImages = 5;

    public id!: number;
    public name!: string;
    public orientation: string|null = null;
    public distance: number|null = null;
    public sectors: PlanetSector[]|null = null;
    public actions: Action[]|null = null;
    public imageId!: number;

    public load(object: any): Planet {
        if (object) {
            this.id = object.id;
            this.name = object.name;
            this.orientation = object.orientation || null;
            this.distance = object.distance || null;
            this.sectors = object.sectors || null;
            this.actions = object.actions || null;
            this.imageId = this.name.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0) % this.numberOfPlanetImages;
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

    public getSmallImage(): string {
        return require(`@/assets/images/astro/planet_${this.imageId}_small.png`);
    }

    public getActionByKey(key: string): Action | null {
        return this.actions?.find(action => action.key === key) || null;
    }
}
