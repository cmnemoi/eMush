export class Skin {
    public skinSlotName: string|null;
    public skinName: string|null;


    constructor() {
        this.skinSlotName = null;
        this.skinName = null;
    }
    load(object: any): Skin {
        if (typeof object !== "undefined") {
            this.skinName = object.skinName;
            this.skinSlotName = object.skinSlotName;
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
}
