
export class RandomItemPlace {
    public iri: string|null;
    public id: number|null;
    public places: Array<any>|null;
    public items: Array<any>|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.places = [];
        this.items = [];
    }
    load(object:any) : RandomItemPlace {
        if (typeof object !== "undefined") {
            this.iri = object.iri;
            this.id = object.id;
            this.places = object.places;
            this.items = object.items;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id,
            'places': this.places,
            'items': this.items
        };
    }
    decode(jsonString : string): RandomItemPlace {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
            this.places = object.places;
            this.items = object.items;
        }

        return this;
    }
}
