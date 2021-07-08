export class Status {
    public id : number|null
    public key : string|null
    public name : string|null
    public charge : number|null
    public description : string|null

    constructor() {
        this.id = null;
        this.key = null;
        this.name = null;
        this.charge = null;
        this.description = null;
    }

    load(object: any) {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.charge = object.charge;
            this.description = object.description;
        }
        return this;
    }
    jsonEncode() {
        return JSON.stringify(this);
    }
    decode(jsonString: any) {
        if (jsonString) {
            let object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
