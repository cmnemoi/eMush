import { getImgUrl } from "@/utils/getImgUrl";

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

    public load(object: any): Planet {
        if (object) {
            this.id = object.id;
            this.imageId = object.imageId;
            this.name = object.name || null;
            this.orientation = object.orientation || null;
            this.distance = object.distance || null;
            this.sectors = object.sectors || null;
            this.actions = object.actions || null;
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
        return getImgUrl(`astro/planet_${this.imageId}_small.png`);
    }

    public getActionByKey(key: string): Action | null {
        return this.actions?.find(action => action.key === key) || null;
    }

    public toString(): string {
        const numberOfSectorsRevealed = this.sectors?.filter(sector => sector.isRevealed).length || 0;
        const sectorCounts = this.getSectorCounts();
        const rawSectorCountsText = this.formatSectorCounts(sectorCounts);
        const sectorCountsText = this.putUnknownSectorsAtEnd(rawSectorCountsText);

        return `:planet: **${this.name}** (${numberOfSectorsRevealed}/${this.sectors?.length})\n*${this?.orientation} - ${this?.distance} :fuel:*\n${sectorCountsText}`;
    }

    private getSectorCounts(): {[key: string]: number} | undefined {
        return this.sectors?.reduce((acc, sector) => {
            acc[sector.name] = (acc[sector.name] || 0) + 1;
            return acc;
        }, {} as {[key: string]: number});
    }

    private formatSectorCounts(sectorCounts: {[key: string]: number} | undefined): string {
        if (!sectorCounts) throw new Error('Sector counts are not defined');
        return Object.entries(sectorCounts).map(([name, count]) => count > 1 ? `${name} (x${count})` : name).join(', ');
    }

    private putUnknownSectorsAtEnd(sectorCountsText: string): string {
        const unknownSectorsCountRegex = / \?\?\? \(x[1-9]{1,2}\),/;
        const match = sectorCountsText.match(unknownSectorsCountRegex);
        if (match) {
            const unknownSectorsCountText = match[0];
            sectorCountsText = sectorCountsText.replace(unknownSectorsCountRegex, '');
            sectorCountsText += ', ' + unknownSectorsCountText.replace(/,/, '').trim();
        }

        return sectorCountsText;
    }

}