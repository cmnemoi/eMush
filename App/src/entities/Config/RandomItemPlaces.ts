
export class RandomItemPlaces {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public places: Array<string>|null;
    public items: Array<string>|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.places = [];
        this.items = [];
    }
    load(object:any) : RandomItemPlaces {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.name = object.name;
            this.places = object.places;
            this.items = object.items;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id,
            'name': this.name,
            'places': this.places,
            'items': this.items
        };
    }
    decode(jsonString : string): RandomItemPlaces {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
