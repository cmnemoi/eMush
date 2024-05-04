import { QuantityPoint } from "@/entities/QuantityPoint";

export class SpecialistPoint {
    public key : string|null;
    public charge: QuantityPoint|null;

    constructor() {
        this.key = null;
        this.charge = null;
    }

    load(object: any): SpecialistPoint {
        if (typeof object !== "undefined") {
            this.key = object.key;
            if (object.quantityPoint) {
                this.charge = (new QuantityPoint()).load(object.quantityPoint);
            }
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: any): SpecialistPoint {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
