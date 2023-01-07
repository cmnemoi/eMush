export class ModifierActivationRequirement {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public activationRequirement: string|null;
    public value: number|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.activationRequirement = null;
        this.value = null;
    }
    load(object:any) : ModifierActivationRequirement {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.name = object.name;
            this.activationRequirement = object.activationRequirement;
            this.value = object.value;
        }
        return this;
    }
    jsonEncode() : string {
        return JSON.stringify(this);
    }
    decode(jsonString : string): ModifierActivationRequirement {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.id = object.id;
            this.name = object.name;
            this.activationRequirement = object.activationRequirement;
            this.value = object.value;
        }

        return this;
    }
}
