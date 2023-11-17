export class SpaceBattleTurret {
    private turretOrders: Map<string, integer> = new Map<string, integer>([
        ['rear_bravo_turret', 1],
        ['centre_bravo_turret', 2],
        ['front_bravo_turret', 3],
        ['rear_alpha_turret', 4],
        ['centre_alpha_turret', 5],
        ['front_alpha_turret', 6]
    ]);

    public id!: number;
    public key!: string;
    public name!: string;
    public charges!: integer;
    public occupiers!: Array<string>;
    public isBroken!: boolean;
    public displayOrder!: integer;

    public constructor() {
        this.occupiers = [];
    }

    public load(object: any): SpaceBattleTurret {
        if (object) {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.charges = object.charges;
            this.occupiers = object.occupiers;
            this.isBroken = object.isBroken;
            this.displayOrder = this.turretOrders.get(this.key) || -1;
        }

        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString : string): SpaceBattleTurret {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
