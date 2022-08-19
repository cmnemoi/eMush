import { ModifierCondition } from "@/entities/Config/ModifierCondition";

export class ModifierConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public delta: number|null;
    public target: string|null;
    public scope: string|null;
    public reach: string|null;
    public mode: string|null;
    public modifierConditions:ModifierCondition[]|null;


    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.delta = null;
        this.target = null;
        this.scope = null;
        this.reach = null;
        this.mode = null;
        this.modifierConditions = null;
    }
    load(object:any) : ModifierConfig {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.name = object.name;
            this.delta = object.delta;
            this.target = object.target;
            this.scope = object.scope;
            this.reach = object.reach;
            this.mode = object.mode;
        }
        return this;
    }
    jsonEncode() : any {
        const modifierConditions : string[] = [];
        this.modifierConditions?.forEach(modifierCondition => (typeof modifierCondition.iri === 'string' ? modifierConditions.push(modifierCondition.iri) : null));
        return {
            'id': this.id,
            'name': this.name,
            'delta': this.delta,
            'target': this.target,
            'scope': this.scope,
            'reach': this.reach,
            'mode': this.mode,
            'modifierConditions': modifierConditions,
        };
    }
    decode(jsonString : string): ModifierConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.id = object.id;
            this.name = object.name;
            this.delta = object.delta;
            this.target = object.target;
            this.scope = object.scope;
            this.reach = object.reach;
            this.mode = object.mode;
        }

        return this;
    }
}
