import { SymptomCondition } from "@/entities/Config/SymptomCondition";

export class SymptomConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public symptomName: string|null;
    public trigger: string|null;
    public visibility: number|null;
    public symptomConditions: SymptomCondition[]|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.symptomName = null;
        this.trigger = null;
        this.visibility = null;
        this.symptomConditions = null;
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
        if (typeof object.symptomCondition !== 'undefined') {
            const symptomConditions : SymptomCondition[] = [];
            object.symptomCondition.forEach((symptomConditionData: any) => {
                const symptomCondition = (new SymptomCondition()).load(symptomConditionData);
                symptomConditions.push(symptomCondition);
            });
            this.symptomConditions = symptomConditions;
        }
        return this;
    }
    jsonEncode() : object {
        const symptomConditions : string[] = [];
        this.symptomConditions?.forEach(symptomCondition => (typeof symptomCondition.iri === 'string' ? symptomConditions.push(symptomCondition.iri) : null));
        return {
            'id': this.id,
            'name': this.name,
            'symptomName': this.symptomName,
            'trigger': this.trigger,
            'visibility': this.visibility,
            'symptomConditions': symptomConditions
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
