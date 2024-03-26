import { Action } from "@/entities/Action";
import { PlanetSector } from "@/entities/PlanetSector";

export class Planet {
    public id!: number;
    public imageId!: number;
    public name: string|null = null;
    public orientation: string|null = null;
    public distance: number|null = null;
    public sectors: PlanetSector[]|null = null;
    public actions: Action[]|null = null;
    public numberOfSectorsRevealed: number|null = null;

    public load(object: any): Planet {
        if (object) {
            this.id = object.id;
            this.imageId = object.imageId;
            this.name = object.name || null;
            this.orientation = object.orientation || null;
            this.distance = object.distance || null;
            this.sectors = object.sectors || null;
            this.actions = object.actions || null;
            this.numberOfSectorsRevealed = this.sectors?.filter(sector => sector.isRevealed).length || null;
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
