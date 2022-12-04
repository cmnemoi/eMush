import { GameConfig } from "@/entities/Config/GameConfig";

export class ConsumableDiseaseConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public diseasesName: Array<any>|null;
    public curesName: Array<any>|null;
    public diseasesChances: Array<any>|null;
    public curesChances: Array<any>|null;
    public diseasesDelayMin: Array<any>|null;
    public diseasesDelayLength: Array<any>|null;
    public effectNumber: Array<any>|null;
    public consumableAttributes: Array<any>|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.diseasesName = [];
        this.curesName = [];
        this.diseasesChances = [];
        this.curesChances = [];
        this.diseasesDelayMin = [];
        this.diseasesDelayLength = [];
        this.effectNumber = [];
        this.consumableAttributes = [];
    }
    load(object:any) : ConsumableDiseaseConfig {
        if (typeof object !== "undefined") {
            this.iri = object.iri;
            this.id = object.id;
            this.name = object.name;
            this.diseasesName = object.diseasesName;
            this.curesName = object.curesName;
            this.diseasesChances = object.diseasesChances;
            this.curesChances = object.curesChances;
            this.diseasesDelayMin = object.diseasesDelayMin;
            this.diseasesDelayLength = object.diseasesDelayLength;
            this.effectNumber = object.effectNumber;
            this.consumableAttributes = object.consumableAttributes;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id,
            'name': this.name,
            'diseasesName': this.diseasesName,
            'curesName': this.curesName,
            'diseasesChances': this.diseasesChances,
            'curesChances': this.curesChances,
            'diseasesDelayMin': this.diseasesDelayMin,
            'diseasesDelayLength': this.diseasesDelayLength,
            'effectNumber': this.effectNumber,
            'consumableAttributes': this.consumableAttributes
        };
    }
    decode(jsonString : string): ConsumableDiseaseConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
            this.name = object.name;
            this.diseasesName = object.diseasesName;
            this.curesName = object.curesName;
            this.diseasesChances = object.diseasesChances;
            this.curesChances = object.curesChances;
            this.diseasesDelayMin = object.diseasesDelayMin;
            this.diseasesDelayLength = object.diseasesDelayLength;
            this.effectNumber = object.effectNumber;
            this.consumableAttributes = object.consumableAttributes;
        }

        return this;
    }
}
