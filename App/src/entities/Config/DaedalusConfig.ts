import { GameConfig } from "@/entities/Config/GameConfig";

export class DaedalusConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public initOxygen: number|null;
    public initFuel: number|null;
    public initHull: number|null;
    public initShield: number|null;
    public randomItemPlace: Array<any>|null;
    public placeConfigs: Array<any>|null;
    public dailySporeNb: number|null;
    public maxOxygen: number|null;
    public maxFuel: number|null;
    public maxHull: number|null;
    public maxShield: number|null;
    public nbMush: number|null;
    public cyclePerGameDay: number|null;
    public cycleLength: number|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.initOxygen = null;
        this.initFuel = null;
        this.initHull = null;
        this.initShield = null;
        this.randomItemPlace = [];
        this.placeConfigs = [];
        this.dailySporeNb = null;
        this.maxOxygen = null;
        this.maxFuel = null;
        this.maxHull = null;
        this.maxShield = null;
        this.nbMush = null;
        this.cyclePerGameDay = null;
        this.cycleLength = null;
    }
    load(object:any) : DaedalusConfig {
        if (typeof object !== "undefined") {
            this.iri = object.iri;
            this.id = object.id;
            this.name = object.name;
            this.initOxygen = object.initOxygen;
            this.initFuel = object.initFuel;
            this.initHull = object.initHull;
            this.initShield = object.initShield;
            this.randomItemPlace = object.randomItemPlace;
            this.placeConfigs = object.placeConfigs;
            this.dailySporeNb = object.dailySporeNb;
            this.maxOxygen = object.maxOxygen;
            this.maxFuel = object.maxFuel;
            this.maxHull = object.maxHull;
            this.maxShield = object.maxShield;
            this.nbMush = object.nbMush;
            this.cyclePerGameDay = object.cyclePerGameDay;
            this.cycleLength = object.cycleLength;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id,
            'name': this.name,
            'initOxygen': this.initOxygen,
            'initFuel': this.initFuel,
            'initHull': this.initHull,
            'initShield': this.initShield,
            'randomItemPlace': this.randomItemPlace,
            'placeConfigs': this.placeConfigs,
            'dailySporeNb': this.dailySporeNb,
            'maxOxygen': this.maxOxygen,
            'maxFuel': this.maxFuel,
            'maxHull': this.maxHull,
            'maxShield': this.maxShield,
            'nbMush': this.nbMush,
            'cyclePerGameDay': this.cyclePerGameDay,
            'cycleLength': this.cycleLength,
        };
    }
    decode(jsonString : string): DaedalusConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
            this.name = object.name;
            this.initOxygen = object.initOxygen;
            this.initFuel = object.initFuel;
            this.initHull = object.initHull;
            this.initShield = object.initShield;
            this.randomItemPlace = object.randomItemPlace;
            this.placeConfigs = object.placeConfigs;
            this.dailySporeNb = object.dailySporeNb;
            this.maxOxygen = object.maxOxygen;
            this.maxFuel = object.maxFuel;
            this.maxHull = object.maxHull;
            this.maxShield = object.maxShield;
            this.nbMush = object.nbMush;
            this.cyclePerGameDay = object.cyclePerGameDay;
            this.cycleLength = object.cycleLength;
        }

        return this;
    }
}
