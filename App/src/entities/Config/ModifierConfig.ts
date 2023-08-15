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
    public applyOnTarget: boolean|null;
    public reverseOnRemove: boolean|null;
    public modifierActivationRequirements:ModifierActivationRequirement[]|null;
    public tagConstraints: Map<string, string>|null;
    public priority: number|null;
    public modifierStrategy: string|null;

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
        this.applyOnTarget = null;
        this.modifierActivationRequirements = null;
        this.tagConstraints = null;
        this.modifierStrategy = null;
        this.priority = null;
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
            this.applyOnTarget = object.applyOnTarget;
            this.triggeredEvent = object.triggeredEvent;
            this.mode = object.mode;
            this.priority = object.priority;
            this.modifierStrategy = object.strategy;
            if (typeof object.triggeredEvent !== "undefined") {
                this.triggeredEvent = (new EventConfig()).load(object.triggeredEvent);
            }
            if (typeof object.tagConstraints !== 'undefined') {
                for (const [key, value] of Object.entries(object.tagConstraints)) {
                    if (typeof value === 'string') {
                        this.tagConstraints?.set(key, value);
                    }
                }
            }
        }
        return this;
    }
    jsonEncode() : any {
        const modifierActivationRequirements : string[] = [];
        this.modifierActivationRequirements?.forEach(modifierActivationRequirement => (typeof modifierActivationRequirement.iri === 'string' ? modifierActivationRequirements.push(modifierActivationRequirement.iri) : null));

        const tagsConstraints : object = {};
        this.tagConstraints?.forEach((value, key) => {
            // @ts-ignore
            tagsConstraints[key] = value;
        });

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
            'applyOnTarget': this.applyOnTarget,
            'modifierActivationRequirements': modifierActivationRequirements,
            'triggeredEvent': this.triggeredEvent?.iri,
            'tagConstraints': tagsConstraints,
            'modifierStrategy': this.modifierStrategy,
            'priority': this.priority,
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
