import { CharacterConfig } from "@/entities/Config/CharacterConfig";
import { DaedalusConfig } from "@/entities/Config/DaedalusConfig";
import { DiseaseCauseConfig } from "@/entities/Config/DiseaseCauseConfig";
import { EquipmentConfig } from "@/entities/Config/EquipmentConfig";
import { StatusConfig } from "@/entities/Config/StatusConfig";

export class GameConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public daedalusConfig: DaedalusConfig|null;
    public characterConfigs: CharacterConfig[]|null;
    public equipmentConfigs: EquipmentConfig[]|null;
    public statusConfigs: StatusConfig[]|null;
    public diseaseCauseConfigs: DiseaseCauseConfig[]|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.daedalusConfig = null;
        this.characterConfigs = null;
        this.equipmentConfigs = null;
        this.statusConfigs = null;
        this.diseaseCauseConfigs = null;
    }
    load(object:any) : GameConfig {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.name = object.name;
            if (typeof object.daedalusConfig !== "undefined") {
                this.daedalusConfig = new DaedalusConfig().load(object.daedalusConfig);
            }
            if (typeof object.characterConfigs !== "undefined") {
                const characterConfigs : CharacterConfig[] = [];
                object.characterConfigs.forEach((characterConfigsData: any) => {
                    const characterConfig = (new CharacterConfig()).load(characterConfigsData);
                    characterConfigs.push(characterConfig);
                });
                this.characterConfigs = characterConfigs;
            }
            if (typeof object.equipmentConfigs !== "undefined") {
                const equipmentConfigs : EquipmentConfig[] = [];
                object.equipmentConfigs.forEach((equipmentConfigsData: any) => {
                    const equipmentConfig = (new EquipmentConfig()).load(equipmentConfigsData);
                    equipmentConfigs.push(equipmentConfig);
                });
                this.equipmentConfigs = equipmentConfigs;
            }
            if (typeof object.statusConfigs !== "undefined") {
                const statusConfigs : StatusConfig[] = [];
                object.statusConfigs.forEach((statusConfigsData: any) => {
                    const statusConfig = (new StatusConfig()).load(statusConfigsData);
                    statusConfigs.push(statusConfig);
                });
                this.statusConfigs = statusConfigs;
            }
            if (typeof object.diseaseCauseConfigs !== "undefined") {
                const diseaseCauseConfigs : DiseaseCauseConfig[] = [];
                object.diseaseCauseConfigs.forEach((diseaseCauseConfigsData: any) => {
                    const diseaseCauseConfig = (new DiseaseCauseConfig()).load(diseaseCauseConfigsData);
                    diseaseCauseConfigs.push(diseaseCauseConfig);
                });
                this.diseaseCauseConfigs = diseaseCauseConfigs;
            }
        }
        return this;
    }
    jsonEncode() : string {
        const characterConfigs : string[] = [];
        this.characterConfigs?.forEach(characterConfig => (typeof characterConfig.iri === 'string' ? characterConfigs.push(characterConfig.iri) : null));
        const equipmentConfigs : string[] = [];
        this.equipmentConfigs?.forEach(equipmentConfig => (typeof equipmentConfig.iri === 'string' ? equipmentConfigs.push(equipmentConfig.iri) : null));
        const statusConfigs : string[] = [];
        this.statusConfigs?.forEach(statusConfig => (typeof statusConfig.iri === 'string' ? statusConfigs.push(statusConfig.iri) : null));
        const diseaseCauseConfigs : string[] = [];
        this.diseaseCauseConfigs?.forEach(diseaseCauseConfig => (typeof diseaseCauseConfig.iri === 'string' ? diseaseCauseConfigs.push(diseaseCauseConfig.iri) : null));
        const data : any = {
            'id': this.id,
            'name': this.name,
            'daedalusConfig': this.daedalusConfig?.iri,
            'characterConfigs': characterConfigs,
            'equipmentConfigs': equipmentConfigs,
            'statusConfigs': statusConfigs,
            'diseaseCauseConfigs': diseaseCauseConfigs,
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
