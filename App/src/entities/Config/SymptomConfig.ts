import { SymptomActivationRequirement } from "@/entities/Config/SymptomActivationRequirement";

export class SymptomConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public symptomName: string|null;
    public trigger: string|null;
    public visibility: number|null;
    public symptomActivationRequirements: SymptomActivationRequirement[]|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.symptomName = null;
        this.trigger = null;
        this.visibility = null;
        this.symptomActivationRequirements = null;
    }
    load(object:any) : SymptomConfig {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.name = object.name;
            this.symptomName = object.symptomName;
            this.trigger = object.trigger;
            this.visibility = object.visibility;
        }
        if (typeof object.symptomActivationRequirements !== 'undefined') {
            const symptomActivationRequirements : SymptomActivationRequirement[] = [];
            object.symptomActivationRequirements.forEach((symptomActivationRequirementData: any) => {
                const symptomActivationRequirement = (new SymptomActivationRequirement()).load(symptomActivationRequirementData);
                symptomActivationRequirements.push(symptomActivationRequirement);
            });
            this.symptomActivationRequirements = symptomActivationRequirements;
        }
        return this;
    }
    jsonEncode() : object {
        const symptomActivationRequirements : string[] = [];
        this.symptomActivationRequirements?.forEach(symptomActivationRequirement => (typeof symptomActivationRequirement.iri === 'string' ? symptomActivationRequirements.push(symptomActivationRequirement.iri) : null));
        return {
            'id': this.id,
            'name': this.name,
            'symptomName': this.symptomName,
            'trigger': this.trigger,
            'visibility': this.visibility,
            'symptomActivationRequirements': symptomActivationRequirements
        };
    }
    decode(jsonString : string): SymptomConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
