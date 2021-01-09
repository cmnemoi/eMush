import { Equipment } from "@/entities/Equipment";

export class Item extends Equipment {
    constructor() {
        super();
        this.number = 0;
    }
    load(object) {
        super.load(object);
        if (typeof object !== "undefined") {
            this.number = object.number;
        }
        return this;
    }
    jsonEncode() {
        return JSON.stringify(this);
    }
    decode(jsonString) {
        if (jsonString) {
            let object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
