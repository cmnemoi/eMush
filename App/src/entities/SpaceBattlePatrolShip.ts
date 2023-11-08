export class SpaceBattlePatrolShip {
    public id!: number;
    public key!: string;
    public name!: string;
    public armor!: integer;
    public charges: integer|null;
    public pilot: string|null;

    public constructor() {
        this.charges = null;
        this.pilot = null;
    }

    public load(object: any): SpaceBattlePatrolShip {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.armor = object.armor;
            this.charges = object.charges;
            this.pilot = object.pilot;
        }

        return this;
    }

    public jsonEncode(): object {
        const data : any = {
            'id': this.id,
            'key': this.key,
            'name': this.name,
            'armor': this.armor,
            'charges': this.charges,
            'pilot': this.pilot
        };

        return data;
    }

    public decode(jsonString : string): SpaceBattlePatrolShip {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }

    public isPasiphae(): boolean {
        return this.key === 'pasiphae';
    }
}
