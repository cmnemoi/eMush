export class RebelBase {
    public key!: string;
    public name!: string;
    public hoverName!: string;
    public description!: string;
    public signal!: string;
    public isContacting: boolean;
    public isLost: boolean;

    constructor() {
        this.isContacting = false;
        this.isLost = false;
    }

    load(object: any): RebelBase {
        if (object) {
            this.key = object.key;
            this.name = object.name;
            this.hoverName = object.hoverName;
            this.description = object.description;
            this.signal = object.signal;
            this.isContacting = object.isContacting;
            this.isLost = object.isLost;
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: any): RebelBase {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
