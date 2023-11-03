import { ModifierConfig } from "@/entities/Config/ModifierConfig";

export class DiseaseConfig {
    public iri: string|null;
    public id: number|null;
    public diseaseName: string|null;
    public name: string|null;
    public type: string|null;
    public modifierConfigs: ModifierConfig[]|null;
    public resistance: number|null;
    public delayMin: number|null;
    public delayLength: number|null;
    public diseasePointMin: number|null;
    public diseasePointLength: number|null;
    public override: Array<string>|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.diseaseName = null;
        this.name = null;
        this.type = null;
        this.modifierConfigs = [];
        this.resistance = null;
        this.delayMin = null;
        this.delayLength = null;
        this.diseasePointMin = null;
        this.diseasePointLength = null;
        this.override = [];
    }
    load(object:any) : DiseaseConfig {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.diseaseName = object.diseaseName;
            this.name = object.name;
            this.type = object.type;
            this.resistance = object.resistance;
            this.delayMin = object.delayMin;
            this.delayLength = object.delayLength;
            this.diseasePointMin = object.diseasePointMin;
            this.diseasePointLength = object.diseasePointLength;
            this.override = object.override;
        }
        if (typeof object.modifierConfig !== 'undefined') {
            const modifierConfigs : ModifierConfig[] = [];
            object.modifierConfig.forEach((modifierConfigData: any) => {
                const modifierConfig = (new ModifierConfig()).load(modifierConfigData);
                modifierConfigs.push(modifierConfig);
            });
            this.modifierConfigs = modifierConfigs;
        }
        return this;
    }
    jsonEncode() : object {
        const modifierConfigs : string[] = [];
        this.modifierConfigs?.forEach(modifierConfig => (typeof modifierConfig.iri === 'string' ? modifierConfigs.push(modifierConfig.iri) : null));
        return {
            'id': this.id,
            'diseaseName': this.diseaseName,
            'name': this.name,
            'type': this.type,
            'modifierConfigs': modifierConfigs,
            'resistance': this.resistance,
            'delayMin': this.delayMin,
            'delayLength': this.delayLength,
            'diseasePointMin': this.diseasePointMin,
            'diseasePointLength': this.diseasePointLength,
            'override': this.override
        };
    }
    decode(jsonString : string): DiseaseConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
