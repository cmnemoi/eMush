import { GameConfig } from "@/entities/Config/GameConfig";

export class CharacterConfig {
    public iri: string|null;
    public id: number|null;
    public gameConfig: GameConfig|null;
    public name: string|null;
    public initStatuses: string|null;
    public actions: array|null;
    public startingItems: array|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.gameConfig = null;
        this.name = null;
        this.initStatuses = null;
        this.actions = [];
        this.startingItems = [];
    }
    load(object:any) : CharacterConfig {
        if (typeof object !== "undefined") {
            this.iri = object.iri;
            this.id = object.id;
            this.gameConfig = object.gameConfig;
            this.name = object.name;
            this.initStatuses = object.initStatuses;
            this.actions = object.actions;
            this.startingItems = object.startingItems;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id,
            'gameConfig': this.gameConfig?.iri,
            'name': this.name,
            'initStatuses': this.initStatuses,
            'actions': this.actions,
            'startingItems': this.startingItems,
        };
    }
    decode(jsonString : string): CharacterConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
            this.gameConfig = object.gameConfig;
            this.name = object.name;
            this.initStatuses = object.initStatuses;
            this.actions = object.actions;
            this.startingItems = object.startingItems;
        }

        return this;
    }
}
