
export class QuantityPoint {
    public name!: string;
    public description: string|null;
    public quantity!: number;
    public max!: number;

    constructor() {
        this.description = null;
    }
    load(object: any): QuantityPoint {
        if (typeof object !== "undefined") {
            this.name = object.name;
            this.description = object.description;
            this.quantity = object.quantity;
            this.max = object.max;
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: string): QuantityPoint {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
