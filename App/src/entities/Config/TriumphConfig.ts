import { GameConfig } from "@/entities/Config/GameConfig";

export class TriumphConfig {
    public iri: string|null;
    public id: number|null;
    public gameConfig: GameConfig|null;
    public name: string|null;
    public triumph: number|null;
    public isAllCrew: boolean|null;
    public team: string|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.gameConfig = null;
        this.name = null;
        this.triumph = null;
        this.isAllCrew = null;
        this.team = null;
    }
    load(object:any) : TriumphConfig {
        if (typeof object !== "undefined") {
            this.iri = object.iri;
            this.id = object.id;
            this.gameConfig = object.gameConfig;
            this.name = object.name;
            this.triumph = object.triumph;
            this.isAllCrew = object.isAllCrew;
            this.team = object.team;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id,
            'gameConfig': this.gameConfig?.iri,
            'name': this.name,
            'triumph': this.triumph,
            'isAllCrew': this.isAllCrew,
            'team': this.team
        };
    }
    decode(jsonString : string): TriumphConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
            this.gameConfig = object.gameConfig;
            this.name = object.name;
            this.triumph = object.triumph;
            this.isAllCrew = object.isAllCrew;
            this.team = object.team;
        }

        return this;
    }
}
