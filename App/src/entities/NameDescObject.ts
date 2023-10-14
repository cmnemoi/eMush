export class NameDescObject {
    public name : string|null;
    public description : string|null;

    constructor() {
        this.name = null;
        this.description = null;
    }

    load(object: any): NameDescObject {
        if (typeof object !== "undefined") {
            this.name = object.name;
            this.description = object.description;
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: any): NameDescObject {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
