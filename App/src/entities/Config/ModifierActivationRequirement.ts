export class ModifierActivationRequirement {
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
    load(object:any) : ModifierActivationRequirement {
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
    jsonEncode() : any {
        return {
            'id': this.id,
            'name': this.name,
            'activationRequirementName': this.activationRequirementName,
            'activationRequirement': this.activationRequirement,
            'value': this.value
        };
    }
    decode(jsonString : string): ModifierActivationRequirement {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
