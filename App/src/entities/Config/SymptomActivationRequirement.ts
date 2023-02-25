export class SymptomActivationRequirement {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public activationRequirementName: string|null;
    public activationRequirement: string|null;
    public value: number|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.activationRequirementName = null;
        this.activationRequirement = null;
        this.value = null;
    }
    load(object:any) : SymptomActivationRequirement {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.name = object.name;
            this.activationRequirementName = object.activationRequirementName;
            this.activationRequirement = object.activationRequirement;
            this.value = object.value;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id,
            'name': this.name,
            'activationRequirementName': this.activationRequirementName,
            'activationRequirement': this.activationRequirement,
            'value': this.value
        };
    }
    decode(jsonString : string): SymptomActivationRequirement {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
            this.name = object.name;
            this.activationRequirementName = object.activationRequirementName;
            this.activationRequirement = object.activationRequirement;
            this.value = object.value;
        }

        return this;
    }
}
