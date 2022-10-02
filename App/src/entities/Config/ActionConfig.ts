export class ActionConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public types: string[]|null;
    public target: string|null;
    public scope: string|null;
    public successRate: number|null;
    public injuryRate: number|null;
    public dirtyRate: number|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.types = null;
        this.target = null;
        this.scope = null;
        this.successRate = null;
        this.injuryRate = null;
        this.dirtyRate = null;
    }
    load(object:any) : ActionConfig {
        if (typeof object !== "undefined") {
            this.iri = object.iri;
            this.id = object.id;
            this.name = object.name;
            this.types = object.types;
            this.target = object.target;
            this.scope = object.scope;
            this.successRate = object.successRate;
            this.injuryRate = object.injuryRate;
            this.dirtyRate = object.dirtyRate;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id,
            'name': this.name,
            'types': this.types,
            'target': this.target,
            'scope': this.scope,
            'successRate': this.successRate,
            'injuryRate': this.injuryRate,
            'dirtyRate': this.dirtyRate
        };
    }
    decode(jsonString : string): ActionConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
            this.name = object.name;
            this.types = object.types;
            this.target = object.target;
            this.scope = object.scope;
            this.successRate = object.successRate;
            this.injuryRate = object.injuryRate;
            this.dirtyRate = object.dirtyRate;
        }

        return this;
    }
}
