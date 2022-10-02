import { GameConfig } from "@/entities/Config/GameConfig";

export class PlaceConfig {
    public iri: string|null;
    public id: number|null;
    public gameConfig: GameConfig|null;
    public name: string|null;
    public type: string|null;
    public doors: array|null;
    public items: array|null;
    public equipments: array|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.gameConfig = null;
        this.name = null;
        this.type = null;
        this.doors = [];
        this.items = [];
        this.equipments = [];
    }
    load(object:any) : PlaceConfig {
        if (typeof object !== "undefined") {
            this.iri = object.iri;
            this.id = object.id;
            this.gameConfig = object.gameConfig;
            this.name = object.name;
            this.type = object.type;
            this.doors = object.doors;
            this.items = object.items;
            this.equipments = object.equipments;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id,
            'gameConfig': this.gameConfig?.iri,
            'name': this.name,
            'type': this.type,
            'doors': this.doors,
            'items': this.items,
            'equipments': this.equipments,
        };
    }
    decode(jsonString : string): PlaceConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
            this.gameConfig = object.gameConfig;
            this.name = object.name;
            this.type = object.type;
            this.doors = object.doors;
            this.items = object.items;
            this.equipments = object.equipments;
        }

        return this;
    }
}
