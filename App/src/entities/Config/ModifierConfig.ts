import { ModifierActivationRequirement } from "@/entities/Config/ModifierActivationRequirement";
import { EventConfig } from "@/entities/Config/EventConfig";

export class ModifierConfig {
    public iri: string|null;
    public id: number|null;
    public type: string|null;
    public name: string|null;
    public modifierName: string|null;
    public delta: number|null;
    public targetVariable: string|null;
    public targetEvent: string|null;
    public modifierRange: string|null;
    public mode: string|null;
    public triggeredEvent: EventConfig|null;
    public applyOnActionParameter: boolean|null;
    public reverseOnRemove: boolean|null;
    public modifierActivationRequirements:ModifierActivationRequirement[]|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.type = null;
        this.name = null;
        this.modifierName = null;
        this.delta = null;
        this.targetVariable = null;
        this.targetEvent = null;
        this.modifierRange = null;
        this.mode = null;
        this.reverseOnRemove = null;
        this.triggeredEvent = null;
        this.applyOnActionParameter = null;
        this.modifierActivationRequirements = null;
    }
    load(object:any) : ModifierConfig {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.type = object['@type'];
            this.id = object.id;
            this.name = object.name;
            this.modifierName = object.modifierName;
            this.delta = object.delta;
            this.targetVariable = object.targetVariable;
            this.targetEvent = object.targetEvent;
            this.modifierRange = object.modifierRange;
            this.reverseOnRemove = object.reverseOnRemove;
            this.applyOnActionParameter = object.applyOnActionParameter;
            this.triggeredEvent = object.triggeredEvent;
            this.mode = object.mode;
            if (typeof object.triggeredEvent !== "undefined") {
                this.triggeredEvent = (new EventConfig()).load(object.triggeredEvent);
            }
        }
        return this;
    }
    jsonEncode() : any {
        const modifierActivationRequirements : string[] = [];
        this.modifierActivationRequirements?.forEach(modifierActivationRequirement => (typeof modifierActivationRequirement.iri === 'string' ? modifierActivationRequirements.push(modifierActivationRequirement.iri) : null));
        return {
            'id': this.id,
            'name': this.name,
            'modifierName': this.modifierName,
            'delta': this.delta,
            'targetVariable': this.targetVariable,
            'targetEvent': this.targetEvent,
            'modifierRange': this.modifierRange,
            'mode': this.mode,
            'reverseOnRemove': this.reverseOnRemove,
            'applyOnActionParameter': this.applyOnActionParameter,
            'modifierActivationRequirements': modifierActivationRequirements,
            'triggeredEvent': this.triggeredEvent?.iri,
        };
    }
    decode(jsonString : string): ModifierConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
