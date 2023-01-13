import { CharacterConfig } from "@/entities/Config/CharacterConfig";
import { DaedalusConfig } from "@/entities/Config/DaedalusConfig";

export class GameConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public daedalusConfig: DaedalusConfig|null;
    public charactersConfig: CharacterConfig[]|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.daedalusConfig = null;
        this.charactersConfig = null;
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
        }
        return this;
    }
    jsonEncode() : string {
        const charactersConfig : string[] = [];
        this.charactersConfig?.forEach(characterConfig => (typeof characterConfig.iri === 'string' ? charactersConfig.push(characterConfig.iri) : null));
        const data : any = {
            'id': this.id,
            'name': this.name,
            'daedalusConfig': this.daedalusConfig?.iri,
            'charactersConfig': charactersConfig,
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
