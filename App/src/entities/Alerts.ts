export class Alert {
    public key: string|null;
    public name: string|null;
    public description: string|null;
    public reports: Array<string>;

    constructor() {
        this.key = null;
        this.name = null;
        this.description = null;
        this.reports = [];
    }
    load(object : any) : Alert {
        if (typeof object !== "undefined") {
            this.key = object.key;
            this.name = object.name;
            this.description = object.description;
            if (typeof object.reports !== 'undefined') {
                this.reports = object.reports;
            }
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: string): Alert {
        if (jsonString) {
            let object = JSON.parse(jsonString);
            this.key = object.key;
            this.name = object.name;
            this.description = object.description;
        }

        return this;
    }
}
