export class ConsumableDiseaseAttribute {
    public iri: string|null;
    public id: integer|null;
    public disease: string|null;
    public type: string|null;
    public rate: integer|null;
    public delayMin: integer|null;
    public delayLength: integer|null;


    constructor() {
        this.iri = null;
        this.id = null;
        this.disease = null;
        this.type = null;
        this.rate = null;
        this.delayMin = null;
        this.delayLength = null;

    }
    load(object:any) : ConsumableDiseaseAttribute {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.disease = object.disease;
            this.type = object.type;
            this.rate = object.rate;
            this.delayMin = object.delayMin;
            this.delayLength = object.delayLength;
        }
        return this;
    }
    jsonEncode() : object {
        const data: any = {
            'id': this.id,
            'disease': this.disease,
            'type': this.type,
            'rate': this.rate,
            'delayMin': this.delayMin,
            'delayLength': this.delayLength
        };
        return data;
    }
    decode(jsonString : string): ConsumableDiseaseAttribute {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
