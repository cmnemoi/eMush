type HexColor = `#${string}`;

type ActopiPlayer = {
    initials: string;
    color: HexColor;
};
export class Minimap {
    public actopi: Array<ActopiPlayer> = [];
    public broken_count = 0;
    public broken_doors: Array<string> = [];
    public broken_equipments: Array<string> = [];
    public fire = false;
    public players_count = 0;
    public name: string;

    constructor() {
        this.name = 'undefined';
    }
    load(object : any) : Minimap {
        if (typeof object !== "undefined") {
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
