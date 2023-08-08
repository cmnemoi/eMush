export class SpaceBattleTurret {
    public id!: number;
    public key!: string;
    public name!: string;
    public charges!: integer;
    public occupiers!: Array<string>;

    public constructor() {
        this.occupiers = [];
    }

    public load(object: any): SpaceBattleTurret {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.charges = object.charges;
            this.occupiers = object.occupiers;
        }
        
        return this;
    }

    public jsonEncode(): object {
        const data : any = {
            'id': this.id,
            'key': this.key,
            'name': this.name,
            'charges': this.charges,
            'occupiers': this.occupiers,
        };

        return data;
    }

    public decode(jsonString : string): SpaceBattleTurret {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}