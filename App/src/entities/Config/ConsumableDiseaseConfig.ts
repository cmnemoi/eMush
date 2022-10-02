import { GameConfig } from "@/entities/Config/GameConfig";

export class ConsumableDiseaseConfig {
    public iri: string|null;
    public id: number|null;
    public gameConfig: GameConfig|null;
    public name: string|null;
    public diseasesName: array|null;
    public curesName: array|null;
    public diseasesChances: array|null;
    public curesChances: array|null;
    public diseasesDelayMin: array|null;
    public diseasesDelayLength: array|null;
    public effectNumber: array|null;
    public consumableAttributes: array|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.gameConfig = null;
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
            this.gameConfig = object.gameConfig;
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
            'gameConfig': this.gameConfig?.iri,
            'name': this.name,
            'diseasesName': this.diseasesName,
            'curesName': this.curesName,
            'diseasesChances': this.diseasesChances,
            'curesChances': this.curesChances,
            'diseasesDelayMin': this.diseasesDelayMin,
            'diseasesDelayLength': this.diseasesDelayLength,
            'effectNumber': this.effectNumber,
            'consumableAttributes': this.consumableAttributes,
        };
    }
    decode(jsonString : string): ConsumableDiseaseConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
            this.gameConfig = object.gameConfig;
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
