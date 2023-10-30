export class Minimap {
    public actopi: Array<number> = [];
    public broken_count = 0;
    public broken_doors: Array<string> = [];
    public broken_equipments: Array<string> = [];
    public fire = false;
    public players_count = 0;
    public name?: string;
    public id?: number;
    public key: string;

    constructor() {
        this.key = 'undefined';
    }
    load(object : any) : Minimap {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.actopi = object.actopi;
            this.broken_count = object.broken_count;
            this.broken_doors = object.broken_doors;
            this.broken_equipments = object.broken_equipments;
            this.fire = object.fire;
            this.players_count = object.players_count;
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: string): Minimap {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.actopi = object.actopi;
            this.broken_count = object.broken_count;
            this.broken_doors = object.broken_doors;
            this.broken_equipments = object.broken_equipments;
            this.fire = object.fire;
            this.players_count = object.players_count;
        }

        return this;
    }
}
