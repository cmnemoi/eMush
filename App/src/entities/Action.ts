export class Action {
    public iri: string|null;
    public id: number|null;
    public key: string|null;
    public canExecute: boolean;
    public name: string|null;
    public description: string|null;
    public actionPointCost: number|null;
    public movementPointCost: number|null;
    public successRate: number|null;
    public toConfirm: boolean|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.key = null;
        this.canExecute = false;
        this.name = null;
        this.description = null;
        this.actionPointCost = null;
        this.movementPointCost = null;
        this.successRate = null;
        this.toConfirm = null;
    }
    load(object:any) : Action {
        if (typeof object !== "undefined") {
            this.iri = object["@id"];
            this.id = object.id;
            this.key = object.key;
            this.canExecute = object.canExecute;
            this.name = object.name;
            this.description = object.description;
            this.actionPointCost = object.actionPointCost;
            this.movementPointCost = object.movementPointCost;
            this.successRate = object.successRate;
            this.toConfirm = object.toConfirm;
        }
        return this;
    }
    jsonEncode() : string {
        return JSON.stringify(this);
    }
    decode(jsonString : string): Action {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.id = object.id;
            this.key = object.key;
            this.canExecute = object.canExecute;
            this.name = object.name;
            this.description = object.description;
            this.actionPointCost = object.actionPointCost;
            this.movementPointCost = object.movementPointCost;
            this.successRate = object.successRate;
            this.toConfirm = object.toConfirm;
        }

        return this;
    }
}
