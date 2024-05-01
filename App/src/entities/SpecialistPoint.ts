import { QuantityPoint } from "@/entities/QuantityPoint";

export class SpecialistPoint {
    public name : string|null;
    public description : string|null;
    public charge: QuantityPoint|null;

    constructor() {
        this.name = null;
        this.description = null;
        this.charge = null;
    }

    load(object: any): SpecialistPoint {
        if (typeof object !== "undefined") {
            this.name = object.name;
            this.description = object.description;
            if (object.quantity) {
                this.charge = (new QuantityPoint()).load(object.quantity);
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
