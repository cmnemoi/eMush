export class Status {
    public id : number|null
    public key! : string
    public name : string|null
    public charge : number|null
    public description : string|null

    constructor() {
        this.id = null;
        this.name = null;
        this.charge = null;
        this.description = null;
    }

    load(object: Status): Status {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.charge = object.charge;
            this.description = object.description;
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: string): Status {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
