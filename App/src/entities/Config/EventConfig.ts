export class EventConfig {
    public iri: string|null;
    public id: number|null;
    public type: string|null;
    public name: string|null;
    public eventName: string|null;
    public quantity: number|null;
    public targetVariable: string|null;
    public variableHolderClass: string|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.type = null;
        this.name = null;
        this.eventName = null;
        this.quantity = null;
        this.targetVariable = null;
        this.variableHolderClass = null;
    }
    load(object:any) : EventConfig {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.type = object['@type'];
            this.id = object.id;
            this.name = object.name;
            this.eventName = object.eventName;
            this.quantity = object.quantity;
            this.targetVariable = object.targetVariable;
            this.variableHolderClass = object.variableHolderClass;
        }
        return this;
    }
    jsonEncode() : any {
        return {
            'id': this.id,
            'name': this.name,
            'eventName': this.eventName,
            'quantity': this.quantity,
            'targetVariable': this.targetVariable,
            'variableHolderClass': this.variableHolderClass
        };
    }
    decode(jsonString : string): EventConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
