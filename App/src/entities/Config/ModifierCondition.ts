export class ModifierCondition {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public condition: string|null;
    public value: number|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.condition = null;
        this.value = null;
    }
    load(object:any) : ModifierCondition {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.name = object.name;
            this.condition = object.condition;
            this.value = object.value;
        }
        return this;
    }
    jsonEncode() : string {
        return JSON.stringify(this);
    }
    decode(jsonString : string): ModifierCondition {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.id = object.id;
            this.name = object.name;
            this.condition = object.condition;
            this.value = object.value;
        }

        return this;
    }
}
