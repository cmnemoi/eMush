export class SymptomCondition {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public conditionName: string|null;
    public condition: string|null;
    public value: number|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.conditionName = null;
        this.condition = null;
        this.value = null;
    }
    load(object:any) : SymptomCondition {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.name = object.name;
            this.conditionName = object.conditionName;
            this.condition = object.condition;
            this.value = object.value;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id,
            'name': this.name,
            'conditionName': this.conditionName,
            'condition': this.condition,
            'value': this.value
        };
    }
    decode(jsonString : string): SymptomCondition {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
            this.name = object.name;
            this.conditionName = object.conditionName;
            this.condition = object.condition;
            this.value = object.value;
        }

        return this;
    }
}
