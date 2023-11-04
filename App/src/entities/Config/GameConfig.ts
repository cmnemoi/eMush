import { CharacterConfig } from "@/entities/Config/CharacterConfig";
import { DaedalusConfig } from "@/entities/Config/DaedalusConfig";
import { EquipmentConfig } from "@/entities/Config/EquipmentConfig";
import { StatusConfig } from "@/entities/Config/StatusConfig";
import { TriumphConfig } from "@/entities/Config/TriumphConfig";
import { DiseaseCauseConfig } from "@/entities/Config/DiseaseCauseConfig";
import { DiseaseConfig } from "@/entities/Config/DiseaseConfig";
import { ConsumableDiseaseConfig } from "@/entities/Config/ConsumableDiseaseConfig";
import { DifficultyConfig } from "@/entities/Config/DifficultyConfig";

export class GameConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public daedalusConfig: DaedalusConfig|null;
    public charactersConfig: CharacterConfig[]|null;
    public equipmentsConfig: EquipmentConfig[]|null;
    public statusConfigs: StatusConfig[]|null;
    public triumphConfig: TriumphConfig[]|null;
    public diseaseCauseConfig: DiseaseCauseConfig[]|null;
    public diseaseConfig: DiseaseConfig[]|null;
    public consumableDiseaseConfig: ConsumableDiseaseConfig[]|null;
    public difficultyConfig: DifficultyConfig|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.daedalusConfig = null;
        this.charactersConfig = null;
        this.equipmentsConfig = null;
        this.statusConfigs = null;
        this.triumphConfig = null;
        this.diseaseCauseConfig = null;
        this.diseaseConfig = null;
        this.consumableDiseaseConfig = null;
        this.difficultyConfig = null;
    }
    load(object:any) : GameConfig {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.name = object.name;
            if (typeof object.daedalusConfig !== "undefined") {
                this.daedalusConfig = new DaedalusConfig().load(object.daedalusConfig);
            }
            if (typeof object.charactersConfig !== "undefined") {
                const charactersConfig : CharacterConfig[] = [];
                object.charactersConfig.forEach((charactersConfigData: any) => {
                    const characterConfig = (new CharacterConfig()).load(charactersConfigData);
                    charactersConfig.push(characterConfig);
                });
                this.charactersConfig = charactersConfig;
            }
            if (typeof object.equipmentsConfig !== "undefined") {
                const equipmentsConfig : EquipmentConfig[] = [];
                object.equipmentsConfig.forEach((equipmentsConfigData: any) => {
                    const equipmentConfig = (new EquipmentConfig()).load(equipmentsConfigData);
                    equipmentsConfig.push(equipmentConfig);
                });
                this.equipmentsConfig = equipmentsConfig;
            }
            if (typeof object.statusConfigs !== "undefined") {
                const statusConfigs : StatusConfig[] = [];
                object.statusConfigs.forEach((statusConfigsData: any) => {
                    const statusConfig = (new StatusConfig()).load(statusConfigsData);
                    statusConfigs.push(statusConfig);
                });
                this.statusConfigs = statusConfigs;
            }
            if (typeof object.triumphConfig !== "undefined") {
                const triumphConfigs : TriumphConfig[] = [];
                object.triumphConfig.forEach((triumphConfigData: any) => {
                    const triumphConfig = (new TriumphConfig()).load(triumphConfigData);
                    triumphConfigs.push(triumphConfig);
                });
                this.triumphConfig = triumphConfigs;
            }
            if (typeof object.diseaseCauseConfig !== "undefined") {
                const diseaseCauseConfigs : DiseaseCauseConfig[] = [];
                object.diseaseCauseConfig.forEach((diseaseCauseConfigData: any) => {
                    const diseaseCauseConfig = (new DiseaseCauseConfig()).load(diseaseCauseConfigData);
                    diseaseCauseConfigs.push(diseaseCauseConfig);
                });
                this.diseaseCauseConfig = diseaseCauseConfigs;
            }
            if (typeof object.diseaseConfig !== "undefined") {
                const diseaseConfigs : DiseaseConfig[] = [];
                object.diseaseConfig.forEach((diseaseConfigData: any) => {
                    const diseaseConfig = (new DiseaseConfig()).load(diseaseConfigData);
                    diseaseConfigs.push(diseaseConfig);
                });
                this.diseaseConfig = diseaseConfigs;
            }
            if (typeof object.consumableDiseaseConfig !== "undefined") {
                const consumableDiseaseConfigs : ConsumableDiseaseConfig[] = [];
                object.consumableDiseaseConfig.forEach((consumableDiseaseConfigData: any) => {
                    const consumableDiseaseConfig = (new ConsumableDiseaseConfig()).load(consumableDiseaseConfigData);
                    consumableDiseaseConfigs.push(consumableDiseaseConfig);
                });
                this.consumableDiseaseConfig = consumableDiseaseConfigs;
            }
            if (typeof object.difficultyConfig !== "undefined") {
                this.difficultyConfig = (new DifficultyConfig()).load(object.difficultyConfig);
            }

        }
        return this;
    }
    jsonEncode() : string {
        const charactersConfig : string[] = [];
        this.charactersConfig?.forEach(characterConfig => (typeof characterConfig.iri === 'string' ? charactersConfig.push(characterConfig.iri) : null));
        const equipmentsConfig : string[] = [];
        this.equipmentsConfig?.forEach(equipmentConfig => (typeof equipmentConfig.iri === 'string' ? equipmentsConfig.push(equipmentConfig.iri) : null));
        const statusConfigs : string[] = [];
        this.statusConfigs?.forEach(statusConfig => (typeof statusConfig.iri === 'string' ? statusConfigs.push(statusConfig.iri) : null));
        const triumphConfigs : string[] = [];
        this.triumphConfig?.forEach(triumphConfig => (typeof triumphConfig.iri === 'string' ? triumphConfigs.push(triumphConfig.iri) : null));
        const diseaseCauseConfigs : string[] = [];
        this.diseaseCauseConfig?.forEach(diseaseCauseConfig => (typeof diseaseCauseConfig.iri === 'string' ? diseaseCauseConfigs.push(diseaseCauseConfig.iri) : null));
        const diseaseConfigs : string[] = [];
        this.diseaseConfig?.forEach(diseaseConfig => (typeof diseaseConfig.iri === 'string' ? diseaseConfigs.push(diseaseConfig.iri) : null));
        const consumableDiseaseConfigs : string[] = [];
        this.consumableDiseaseConfig?.forEach(consumableDiseaseConfig => (typeof consumableDiseaseConfig.iri === 'string' ? consumableDiseaseConfigs.push(consumableDiseaseConfig.iri) : null));
        const data : any = {
            'id': this.id,
            'name': this.name,
            'daedalusConfig': this.daedalusConfig?.iri,
            'charactersConfig': charactersConfig,
            'equipmentsConfig': equipmentsConfig,
            'statusConfigs': statusConfigs,
            'triumphConfig': triumphConfigs,
            'diseaseCauseConfig': diseaseCauseConfigs,
            'diseaseConfig': diseaseConfigs,
            'consumableDiseaseConfig': consumableDiseaseConfigs,
            'difficultyConfig': this.difficultyConfig?.iri
        };

        return data;
    }
    decode(jsonString : string): GameConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
