import { ConsumableDiseaseConfig } from "@/entities/Config/ConsumableDiseaseConfig";

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
    private loadMapAttribute(object: any, attributeName: string, map: Map<any, number>) {
        if (typeof object[attributeName] !== 'undefined') {
            for (const [key, value] of Object.entries(object[attributeName])) {
                if (typeof key === 'string' && typeof value === 'number') {
                    map.set(key, value);
                }
            }
        }
    }
    private encodeMapAttribute(data: any, attributeName: string, map: Map<any, number>) {
        const mapObject : object = {};
        map.forEach((value, key) => {
            // @ts-ignore
            mapObject[key] = value;
        });
        data[attributeName] = mapObject;
    }
}
