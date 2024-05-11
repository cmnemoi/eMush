import { ActionVariables } from "@/entities/Config/ActionVariables";
import { toHandlers } from "vue";

export class ActionConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public actionName: string|null;
    public types: string[]|null;
    public target: string|null;
    public scope: string|null;
    public actionVariablesArray: ActionVariables|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.actionName = null;
        this.types = null;
        this.target = null;
        this.scope = null;
        this.actionVariablesArray = null;
    }
    load(object:any) : ActionConfig {
        if (typeof object !== "undefined") {
            this.iri = object.iri;
            this.id = object.id;
            this.name = object.name;
            this.actionName = object.actionName;
            this.types = object.types;
            this.target = object.displayHolder;
            this.scope = object.range;
            this.actionVariablesArray = (new ActionVariables()).load(object.actionVariablesArray);
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id,
            'name': this.name,
            'actionName': this.actionName,
            'types': this.types,
            'target': this.target,
            'scope': this.scope,
            'actionVariablesArray': this.actionVariablesArray
        };
    }
    decode(jsonString : string): ActionConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
            this.name = object.name;
            this.actionName = object.actionName;
            this.types = object.types;
            this.target = object.target;
            this.scope = object.scope;
            this.actionVariablesArray = (new ActionVariables()).load(object.actionVariablesArray);
        }

        return this;
    }
}
