
export class Mechanics {
    public iri: string|null;
    public id: number|null;
    public name: string|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
    }
    load(object:any) : Mechanics {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.name = object.name;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id,
            'name': this.name,
        };
    }
    decode(jsonString : string): Mechanics {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
