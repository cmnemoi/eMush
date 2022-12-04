import { GameConfig } from "@/entities/Config/GameConfig";

export class DaedalusConfig {
    public iri: string|null;
    public id: number|null;
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
    public nbMush: number|null;

    constructor() {
        this.iri = null;
        this.id = null;
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
        this.nbMush = null;
    }
    load(object:any) : DaedalusConfig {
        if (typeof object !== "undefined") {
            this.iri = object.iri;
            this.id = object.id;
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
            this.nbMush = object.nbMush;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id,
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
            'nbMush': this.nbMush
        };
    }
    decode(jsonString : string): DaedalusConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
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
            this.nbMush = object.nbMush;
        }

        return this;
    }
}
