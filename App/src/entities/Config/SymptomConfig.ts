export class SymptomConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public trigger: string|null;
    public visibility: number|null;
    public symptomConditions: Array<any>|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.trigger = null;
        this.visibility = null;
        this.symptomConditions = [];
    }
    load(object:any) : SymptomConfig {
        if (typeof object !== "undefined") {
            this.iri = object.iri;
            this.id = object.id;
            this.name = object.name;
            this.trigger = object.trigger;
            this.visibility = object.visibility;
            this.symptomConditions = object.symptomConditions;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id,
            'name': this.name,
            'trigger': this.trigger,
            'visibility': this.visibility,
            'symptomConditions': this.symptomConditions
        };
    }
    decode(jsonString : string): SymptomConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
            this.name = object.name;
            this.trigger = object.trigger;
            this.visibility = object.visibility;
            this.symptomConditions = object.symptomConditions;
        }

        return this;
    }
}
