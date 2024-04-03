export class SpaceBattlePatrolShip {
    public id!: number;
    public key!: string;
    public name!: string;
    public armor: integer|null;
    public charges: integer|null;
    public pilot: string|null;
    public isBroken!: boolean;

    public constructor() {
        this.armor = null;
        this.charges = null;
        this.pilot = null;
    }

    public load(object: any): SpaceBattlePatrolShip {
        if (object) {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.armor = object.armor;
            this.charges = object.charges;
            this.pilot = object.pilot;
            this.isBroken = object.isBroken;
        }

        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
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
