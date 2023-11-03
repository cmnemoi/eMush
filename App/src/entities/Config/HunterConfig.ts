import { StatusConfig } from "./StatusConfig";

export class HunterConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public hunterName: string|null;
    public initialHealth: number|null;
    public damageRange: Map<number, number>|null;
    public hitChance: number|null;
    public dodgeChance: number|null;
    public drawCost: number|null;
    public maxPerWave: number|null;
    public drawWeight: number|null;
    public spawnDifficulty: number|null;
    public initialStatuses: StatusConfig[]|null;
    public scrapDropTable: Map<string, integer>|null;
    public numberOfDroppedScrap: Map<integer, integer>|null;
    public bonusAfterFailedShot: number|null;
    public numberOfActionsPerCycle: number|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.hunterName = null;
        this.initialHealth = null;
        this.damageRange = new Map<number, number>();
        this.hitChance = null;
        this.dodgeChance = null;
        this.drawCost = null;
        this.maxPerWave = null;
        this.drawWeight = null;
        this.spawnDifficulty = null;
        this.initialStatuses = [];
        this.scrapDropTable = new Map<string, integer>();
        this.numberOfDroppedScrap = new Map<integer, integer>();
        this.bonusAfterFailedShot = null;
        this.numberOfActionsPerCycle = null;
    }
    load(object:any) : HunterConfig {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object['id'];
            this.name = object['name'];
            this.hunterName = object['hunterName'];
            this.initialHealth = object['initialHealth'];
            this.hitChance = object['hitChance'];
            this.dodgeChance = object['dodgeChance'];
            this.drawCost = object['drawCost'];
            this.maxPerWave = object['maxPerWave'];
            this.drawWeight = object['drawWeight'];
            this.spawnDifficulty = object['spawnDifficulty'];
            this.bonusAfterFailedShot = object['bonusAfterFailedShot'];
            this.numberOfActionsPerCycle = object['numberOfActionsPerCycle'];
            if (typeof object.damageRange !== 'undefined') {
                for (const [key, value] of Object.entries(object.damageRange)) {
                    if (typeof key === 'string' && typeof value === 'number') {
                        this.damageRange?.set(Number(key), value);
                    }
                }
            }
            if (typeof object.initialStatuses !== "undefined") {
                const initialStatuses : StatusConfig[] = [];
                object.initialStatuses.forEach((initialStatusesData: any) => {
                    const statusConfig = (new StatusConfig()).load(initialStatusesData);
                    initialStatuses.push(statusConfig);
                });
                this.initialStatuses = initialStatuses;
            }
            if (typeof object.scrapDropTable !== 'undefined') {
                for (const [key, value] of Object.entries(object.scrapDropTable)) {
                    if (typeof key === 'string' && typeof value === 'number') {
                        this.scrapDropTable?.set(key, value);
                    }
                }
            }
            if (typeof object.numberOfDroppedScrap !== 'undefined') {
                for (const [key, value] of Object.entries(object.numberOfDroppedScrap)) {
                    if (typeof key === 'string' && typeof value === 'number') {
                        this.numberOfDroppedScrap?.set(Number(key), value);
                    }
                }
            }
        }
        return this;
    }
    jsonEncode() : any {
        const damageRange : object = {};
        this.damageRange?.forEach((value, key) => {
            // @ts-ignore
            damageRange[key] = value;
        });
        const initialStatuses : string[] = [];
        this.initialStatuses?.forEach(statusConfig => (typeof statusConfig.iri === 'string' ? initialStatuses.push(statusConfig.iri) : null));
        const scrapDropTable : object = {};
        this.scrapDropTable?.forEach((value, key) => {
            // @ts-ignore
            scrapDropTable[key] = value;
        });
        const numberOfDroppedScrap : object = {};
        this.numberOfDroppedScrap?.forEach((value, key) => {
            // @ts-ignore
            numberOfDroppedScrap[key] = value;
        });
        return {
            "id": this.id,
            "name": this.name,
            "hunterName": this.hunterName,
            "initialHealth": this.initialHealth,
            "damageRange": damageRange,
            "hitChance": this.hitChance,
            "dodgeChance": this.dodgeChance,
            "drawCost": this.drawCost,
            "maxPerWave": this.maxPerWave,
            "drawWeight": this.drawWeight,
            "spawnDifficulty": this.spawnDifficulty,
            "initialStatuses": initialStatuses,
            "scrapDropTable": scrapDropTable,
            "numberOfDroppedScrap": numberOfDroppedScrap,
            "bonusAfterFailedShot": this.bonusAfterFailedShot,
            "numberOfActionsPerCycle": this.numberOfActionsPerCycle
        };
    }
    decode(jsonString : string): HunterConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }
        return this;
    }
}
