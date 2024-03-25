export class Alert {
    public key!: string;
    public prefix!: string;
    public name: string|null;
    public description: string|null;
    public reports: Array<string>;
    public lostPlayer: string|null = null;

    constructor() {
        this.name = null;
        this.description = null;
        this.reports = [];
    }
    load(object : any) : Alert {
        if (typeof object !== "undefined") {
            this.key = object.key;
            this.prefix = object.prefix;
            this.name = object.name;
            this.description = object.description;
            if (typeof object.reports !== 'undefined') {
                this.reports = object.reports;
            }
            this.lostPlayer = object.lostPlayer;
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: string): Alert {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.key = object.key;
            this.prefix = object.prefix;
            this.name = object.name;
            this.description = object.description;
            this.reports = object.reports;
            this.lostPlayer = object.lostPlayer;
        }

        return this;
    }
}
