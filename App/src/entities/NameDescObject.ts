type NameDescObjectData = {
    id?: string;
    name?: string;
    description?: string;
};

export class NameDescObject {
    public id: string;
    public name : string|null;
    public description : string|null;

    constructor() {
        this.id = "";
        this.name = null;
        this.description = null;
    }

    load(object: NameDescObjectData): NameDescObject {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.name = object.name;
            this.description = object.description;
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: string): NameDescObject {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
