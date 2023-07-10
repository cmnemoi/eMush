export class SpaceBattlePatrolShip {
    public id!: number;
    public name!: string;
    public armor!: integer;
    public charges: integer|null;
    public pilot!: string;

    public constructor() {
        this.charges = null;
    }

    public load(object: any): SpaceBattlePatrolShip {
        if (typeof object !== "undefined") {
            this.id = object.id;
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
            'name': this.name,
            'armor': this.armor,
            'charges': this.charges,
            'pilot': this.pilot,
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
}