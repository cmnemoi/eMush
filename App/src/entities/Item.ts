import { Equipment } from "@/entities/Equipment";

export class Item extends Equipment {
    public number: number

    constructor() {
        super();
        this.number = 0;
    }
    load(object: any): Item {
        super.load(object);
        if (typeof object !== "undefined") {
            this.number = object.number;
        }
        return this;
    }
}
