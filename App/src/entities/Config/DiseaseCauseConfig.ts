export class DiseaseCauseConfig {
    public iri: string|null;
    public id: number|null;
    public causeName: string|null;
    public diseases: Map<string, integer>|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.causeName = null;
        this.diseases = new Map();
    }
    load(object:any) : DiseaseCauseConfig {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.causeName = object.causeName;
            if (typeof object.diseases !== 'undefined') {
                for (const [key, value] of Object.entries(object.diseases)) {
                    if (typeof key === 'string' && typeof value === 'number') {
                        this.diseases?.set(key, value);
                    }
                }
            }
        }
        return this;
    }
    jsonEncode() : object {
        const diseases : object = {};
        this.diseases?.forEach((value, key) => {
            // @ts-ignore
            diseases[key] = value;
        });
        return {
            'id': this.id,
            'causeName': this.causeName,
            'diseases': diseases
        };
    }
    decode(jsonString : string): DiseaseCauseConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
