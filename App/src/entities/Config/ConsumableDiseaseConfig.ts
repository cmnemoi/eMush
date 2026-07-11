export type ConsumableDiseaseConfigData = {
    "@id"?: string;
    id?: number;
    name?: string;
    causeName?: string;
    diseasesName?: Map<string, integer>;
    curesName?: Map<string, integer>;
    diseasesChances?: Map<integer, integer>;
    curesChances?: Map<integer, integer>;
    diseasesDelayMin?: Map<integer, integer>;
    diseasesDelayLength?: Map<integer, integer>;
    effectNumber?: Map<integer, integer>;
};

export class ConsumableDiseaseConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public causeName: string|null;
    public diseasesName: Map<string, integer>|null;
    public curesName: Map<string, integer>|null;
    public diseasesChances: Map<integer, integer>|null;
    public curesChances: Map<integer, integer>|null;
    public diseasesDelayMin: Map<integer, integer>|null;
    public diseasesDelayLength: Map<integer, integer>|null;
    public effectNumber: Map<integer, integer>|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.causeName = null;
        this.diseasesName = new Map();
        this.curesName = new Map();
        this.diseasesChances = new Map();
        this.curesChances = new Map();
        this.diseasesDelayMin = new Map();
        this.diseasesDelayLength = new Map();
        this.effectNumber = new Map();
    }
    load(object:ConsumableDiseaseConfigData) : ConsumableDiseaseConfig {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.name = object.name;
            this.causeName = object.causeName;
            this.loadMapAttribute(object, 'diseasesName', this.diseasesName);
            this.loadMapAttribute(object, 'curesName', this.curesName);
            this.loadMapAttribute(object, 'diseasesChances', this.diseasesChances);
            this.loadMapAttribute(object, 'curesChances', this.curesChances);
            this.loadMapAttribute(object, 'diseasesDelayMin', this.diseasesDelayMin);
            this.loadMapAttribute(object, 'diseasesDelayLength', this.diseasesDelayLength);
            this.loadMapAttribute(object, 'effectNumber', this.effectNumber);
        }
        return this;
    }
    jsonEncode() : object {
        const data = {
            'id': this.id,
            'name': this.name,
            'causeName': this.causeName
        };
        this.encodeMapAttribute(data, 'diseasesName', this.diseasesName);
        this.encodeMapAttribute(data, 'curesName', this.curesName);
        this.encodeMapAttribute(data, 'diseasesChances', this.diseasesChances);
        this.encodeMapAttribute(data, 'curesChances', this.curesChances);
        this.encodeMapAttribute(data, 'diseasesDelayMin', this.diseasesDelayMin);
        this.encodeMapAttribute(data, 'diseasesDelayLength', this.diseasesDelayLength);
        this.encodeMapAttribute(data, 'effectNumber', this.effectNumber);

        return data;
    }
    decode(jsonString : string): ConsumableDiseaseConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
    private loadMapAttribute<K>(object: Record<string, unknown>, attributeName: string, map: Map<K, number> | null) {
        if (map === null) {
            return;
        }

        if (typeof object[attributeName] !== 'undefined') {
            for (const [key, value] of Object.entries(object[attributeName] as Record<string, unknown>)) {
                if (typeof key === 'string' && typeof value === 'number') {
                    map.set(key as unknown as K, value);
                }
            }
        }
    }
    private encodeMapAttribute<K>(data: Record<string, unknown>, attributeName: string, map: Map<K, number> | null) {
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
