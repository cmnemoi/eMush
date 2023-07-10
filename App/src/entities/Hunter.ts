export class Hunter {
    public id!: number;
    public key!: string;
    public name!: string;
    public description!: string;
    public health!: integer;
    public charges: integer|null;

    constructor() {
        this.charges = null;
    }

    public load(object: any): Hunter {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.description = object.description;
            this.health = object.health;
            this.charges = object.charges;
        }
        
        return this;
    }

    public jsonEncode(): object {
        const data : any = {
            'id': this.id,
            'key': this.key,
            'name': this.name,
            'description': this.description,
            'health': this.health,
            'charges': this.charges,
        };

        return data;
    }

    public decode(jsonString : string): Hunter {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}