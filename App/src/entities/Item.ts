import { Equipment } from "@/entities/Equipment";

export class Item extends Equipment {
    public number: number

    constructor() {
        super();
        this.number = 0;
    }
    load(object: any) {
        super.load(object);
        if (typeof object !== "undefined") {
            this.number = object.number;
        }
        return this;
    }
    jsonEncode() {
        return JSON.stringify(this);
    }
    decode(jsonString: string) {
        if (jsonString) {
            let object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
