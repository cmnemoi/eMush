import { CharacterConfig } from "@/entities/Config/CharacterConfig";
import { DaedalusConfig } from "@/entities/Config/DaedalusConfig";
import { EquipmentConfig } from "@/entities/Config/EquipmentConfig";

export class GameConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public daedalusConfig: DaedalusConfig|null;
    public charactersConfig: CharacterConfig[]|null;
    public equipmentsConfig: EquipmentConfig[]|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.daedalusConfig = null;
        this.charactersConfig = null;
        this.equipmentsConfig = null;
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
        }
        return this;
    }
    jsonEncode() : string {
        const charactersConfig : string[] = [];
        this.charactersConfig?.forEach(characterConfig => (typeof characterConfig.iri === 'string' ? charactersConfig.push(characterConfig.iri) : null));
        const equipmentsConfig : string[] = [];
        this.equipmentsConfig?.forEach(equipmentConfig => (typeof equipmentConfig.iri === 'string' ? equipmentsConfig.push(equipmentConfig.iri) : null));
        const data : any = {
            'id': this.id,
            'name': this.name,
            'daedalusConfig': this.daedalusConfig?.iri,
            'charactersConfig': charactersConfig,
            'equipmentsConfig': equipmentsConfig
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
