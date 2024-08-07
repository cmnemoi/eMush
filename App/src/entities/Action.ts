type ActionProvider = {
    id: number;
    class: string;
};

export class Action {
    public iri: string|null;
    public id: number|null;
    public key: string|null;
    public canExecute: boolean;
    public name: string|null;
    public description: string|null;
    public actionPointCost: number|null;
    public movementPointCost: number|null;
    public moralePointCost: number|null;
    public successRate: number|null;
    public confirmation: string|null;
    public actionProvider!: ActionProvider;
    public skillPointCosts: Array<string>;

    constructor() {
        this.iri = null;
        this.id = null;
        this.key = null;
        this.canExecute = false;
        this.name = null;
        this.description = null;
        this.actionPointCost = null;
        this.movementPointCost = null;
        this.moralePointCost = null;
        this.successRate = null;
        this.confirmation = null;
        this.skillPointCosts = [];
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
            this.moralePointCost = object.moralPointCost;
            this.successRate = object.successRate;
            this.confirmation = object.confirmation;
            this.skillPointCosts = object.skillPointCosts;
            this.actionProvider = object.actionProvider;
        }
        return this;
    }
    jsonEncode() : string {
        return JSON.stringify(this);
    }
    decode(jsonString : string): Action {
        if (jsonString) {
            const actionData = JSON.parse(jsonString);
            this.load(actionData);
        }

        return this;
    }
}
