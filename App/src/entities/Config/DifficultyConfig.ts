export class DifficultyConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public equipmentBreakRate: number|null;
    public doorBreakRate: number|null;
    public equipmentFireBreakRate: number|null;
    public startingFireRate: number|null;
    public propagatingFireRate: number|null;
    public hullFireDamageRate: number|null;
    public tremorRate: number|null;
    public electricArcRate: number|null;
    public metalPlateRate: number|null;
    public panicCrisisRate: number|null;
    public firePlayerDamage: Map<integer, integer>|null;
    public fireHullDamage: Map<integer, integer>|null;
    public electricArcPlayerDamage: Map<integer, integer>|null;
    public tremorPlayerDamage: Map<integer, integer>|null;
    public metalPlatePlayerDamage: Map<integer, integer>|null;
    public panicCrisisPlayerDamage: Map<integer, integer>|null;
    public plantDiseaseRate: number|null;
    public cycleDiseaseRate: number|null;
    public difficultyModes: Map<string, integer>|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.equipmentBreakRate = null;
        this.doorBreakRate = null;
        this.equipmentFireBreakRate = null;
        this.startingFireRate = null;
        this.propagatingFireRate = null;
        this.hullFireDamageRate = null;
        this.tremorRate = null;
        this.electricArcRate = null;
        this.metalPlateRate = null;
        this.panicCrisisRate = null;
        this.firePlayerDamage = new Map<integer, integer>();
        this.fireHullDamage = new Map<integer, integer>();
        this.electricArcPlayerDamage = new Map<integer, integer>();
        this.tremorPlayerDamage = new Map<integer, integer>();
        this.metalPlatePlayerDamage = new Map<integer, integer>();
        this.panicCrisisPlayerDamage = new Map<integer, integer>();
        this.plantDiseaseRate = null;
        this.cycleDiseaseRate = null;
        this.difficultyModes = new Map<string, integer>();
    }
    load(object:any) : DifficultyConfig {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.name = object.name;
            this.equipmentBreakRate = object.equipmentBreakRate;
            this.doorBreakRate = object.doorBreakRate;
            this.equipmentFireBreakRate = object.equipmentFireBreakRate;
            this.startingFireRate = object.startingFireRate;
            this.propagatingFireRate = object.propagatingFireRate;
            this.hullFireDamageRate = object.hullFireDamageRate;
            this.tremorRate = object.tremorRate;
            this.electricArcRate = object.electricArcRate;
            this.metalPlateRate = object.metalPlateRate;
            this.panicCrisisRate = object.panicCrisisRate;
            this.loadMapAttribute(object, 'firePlayerDamage', this.firePlayerDamage);
            this.loadMapAttribute(object, 'fireHullDamage', this.fireHullDamage);
            this.loadMapAttribute(object, 'electricArcPlayerDamage', this.electricArcPlayerDamage);
            this.loadMapAttribute(object, 'tremorPlayerDamage', this.tremorPlayerDamage);
            this.loadMapAttribute(object, 'metalPlatePlayerDamage', this.metalPlatePlayerDamage);
            this.loadMapAttribute(object, 'panicCrisisPlayerDamage', this.panicCrisisPlayerDamage);
            this.plantDiseaseRate = object.plantDiseaseRate;
            this.cycleDiseaseRate = object.cycleDiseaseRate;
            this.loadMapAttribute(object, 'difficultyModes', this.difficultyModes);
        }
        return this;
    }
    jsonEncode() : object {
        const data: any = {
            'id': this.id,
            'name': this.name,
            'equipmentBreakRate': this.equipmentBreakRate,
            'doorBreakRate': this.doorBreakRate,
            'equipmentFireBreakRate': this.equipmentFireBreakRate,
            'startingFireRate': this.startingFireRate,
            'propagatingFireRate': this.propagatingFireRate,
            'hullFireDamageRate': this.hullFireDamageRate,
            'tremorRate': this.tremorRate,
            'electricArcRate': this.electricArcRate,
            'metalPlateRate': this.metalPlateRate,
            'panicCrisisRate': this.panicCrisisRate,
            'plantDiseaseRate': this.plantDiseaseRate,
            'cycleDiseaseRate': this.cycleDiseaseRate,
        };

        this.encodeMapAttribute(data, 'firePlayerDamage', this.firePlayerDamage);
        this.encodeMapAttribute(data, 'fireHullDamage', this.fireHullDamage);
        this.encodeMapAttribute(data, 'electricArcPlayerDamage', this.electricArcPlayerDamage);
        this.encodeMapAttribute(data, 'tremorPlayerDamage', this.tremorPlayerDamage);
        this.encodeMapAttribute(data, 'metalPlatePlayerDamage', this.metalPlatePlayerDamage);
        this.encodeMapAttribute(data, 'panicCrisisPlayerDamage', this.panicCrisisPlayerDamage);
        this.encodeMapAttribute(data, 'difficultyModes', this.difficultyModes);

        return data;
    }
    decode(jsonString : string): DifficultyConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }

    private loadMapAttribute(object: any, attributeName: string, map: Map<any, number>|null) {
        if (map === null) {
            return;
        }
        if (typeof object[attributeName] !== 'undefined') {
            for (const [key, value] of Object.entries(object[attributeName])) {
                if (typeof key === 'string' && typeof value === 'number') {
                    map.set(key, value);
                }
            }
        }
    }
    private encodeMapAttribute(data: any, attributeName: string, map: Map<any, number>|null) {
        if (map === null) {
            return;
        }
        const mapObject : object = {};
        map.forEach((value, key) => {
            // @ts-ignore
            mapObject[key] = value;
        });
        data[attributeName] = mapObject;
    }
}
