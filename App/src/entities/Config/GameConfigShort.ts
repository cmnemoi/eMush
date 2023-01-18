import { CharacterConfig } from "@/entities/Config/CharacterConfig";
import { DaedalusConfig } from "@/entities/Config/DaedalusConfig";
import { EquipmentConfig } from "@/entities/Config/EquipmentConfig";
import { StatusConfig } from "@/entities/Config/StatusConfig";

export class GameConfigShort {
    public iri: string|null;
    public id: number|null;
    public name: string|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
    }
    load(object:any) : GameConfigShort {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.name = object.name;
        }
        return this;
    }
    jsonEncode() : string {
        const data : any = {
            'id': this.id,
            'name': this.name,
        };

        return data;
    }
    decode(jsonString : string): GameConfigShort {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
