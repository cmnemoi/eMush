export class GameConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
    }
    load(object:any) : GameConfig {
        if (typeof object !== "undefined") {
            this.iri = object.iri;
            this.id = object.id;
            this.name = object.name;
        }
        return this;
    }
    jsonEncode() : string {
        return JSON.stringify(this);
    }
    decode(jsonString : string): GameConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
            this.name = object.name;
        }

        return this;
    }
}
