export class ActionCost {
    public iri: string|null;
    public id: number|null;
    public actionPointCost: number|null;
    public movementPointCost: number|null;
    public moralPointCost: number|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.actionPointCost = null;
        this.movementPointCost = null;
        this.moralPointCost = null;
    }
    load(object:any) : ActionCost {
        if (typeof object !== "undefined") {
            this.iri = object.iri;
            this.id = object.id;
            this.actionPointCost = object.actionPointCost;
            this.movementPointCost = object.movementPointCost;
            this.moralPointCost = object.moralPointCost;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id,
            'actionPointCost': this.actionPointCost,
            'movementPointCost': this.movementPointCost,
            'moralPointCost': this.moralPointCost
        };
    }
    decode(jsonString : string): ActionCost {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
            this.actionPointCost = object.actionPointCost;
            this.movementPointCost = object.movementPointCost;
            this.moralPointCost = object.moralPointCost;
        }

        return this;
    }
}
