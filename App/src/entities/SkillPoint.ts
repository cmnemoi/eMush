import { QuantityPoint } from "@/entities/QuantityPoint";

type SkillPointData = {
    key?: string;
    quantityPoint?: QuantityPoint;
};

export class SkillPoint {
    public key : string|null;
    public charge: QuantityPoint|null;

    constructor() {
        this.key = null;
        this.charge = null;
    }

    load(object: SkillPointData): SkillPoint {
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
    decode(jsonString: string): SkillPoint {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
