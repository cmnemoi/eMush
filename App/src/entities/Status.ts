export class Status {
    public id : number|null;
    public key! : string;
    public name : string|null;
    public charge : number|null;
    public description : string|null;
    public isPrivate : boolean|null;
    public target : { "key" : string, "id" : number } | null;
    public diseaseType : string | null;

    constructor() {
        this.id = null;
        this.name = null;
        this.charge = null;
        this.description = null;
        this.isPrivate = null;
        this.target = null;
        this.diseaseType = null;
    }

    load(object: any): Status {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.charge = object.charge;
            this.description = object.description;
            this.isPrivate = object.isPrivate;
            this.target = object.target;

            if (object.hasOwnProperty('type')) {
                this.diseaseType = object.type;
            }
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: any): Status {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
