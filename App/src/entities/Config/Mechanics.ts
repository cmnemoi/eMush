
export class Mechanics {
    public iri: string|null;
    public id: number|null;

    constructor() {
        this.iri = null;
        this.id = null;
    }
    load(object:any) : Mechanics {
        if (typeof object !== "undefined") {
            this.iri = object.iri;
            this.id = object.id;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id
        };
    }
    decode(jsonString : string): Mechanics {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
        }

        return this;
    }
}
