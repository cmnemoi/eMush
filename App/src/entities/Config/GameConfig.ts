import { CharacterConfig } from "@/entities/Config/CharacterConfig";
import { DaedalusConfig } from "@/entities/Config/DaedalusConfig";
import { EquipmentConfig } from "@/entities/Config/EquipmentConfig";
import { StatusConfig } from "@/entities/Config/StatusConfig";

export class GameConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public daedalusConfig: DaedalusConfig|null;
    public characterConfigs: CharacterConfig[]|null;
    public equipmentsConfig: EquipmentConfig[]|null;
    public statusConfigs: StatusConfig[]|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.daedalusConfig = null;
        this.characterConfigs = null;
        this.equipmentsConfig = null;
        this.statusConfigs = null;
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
        }
        return this;
    }
    jsonEncode() : string {
        const characterConfigs : string[] = [];
        this.characterConfigs?.forEach(characterConfig => (typeof characterConfig.iri === 'string' ? characterConfigs.push(characterConfig.iri) : null));
        const equipmentsConfig : string[] = [];
        this.equipmentsConfig?.forEach(equipmentConfig => (typeof equipmentConfig.iri === 'string' ? equipmentsConfig.push(equipmentConfig.iri) : null));
        const statusConfigs : string[] = [];
        this.statusConfigs?.forEach(statusConfig => (typeof statusConfig.iri === 'string' ? statusConfigs.push(statusConfig.iri) : null));
        const data : any = {
            'id': this.id,
            'name': this.name,
            'daedalusConfig': this.daedalusConfig?.iri,
            'characterConfigs': characterConfigs,
            'equipmentsConfig': equipmentsConfig,
            'statusConfigs': statusConfigs,
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
