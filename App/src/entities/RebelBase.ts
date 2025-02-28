export class RebelBase {
    public key : string|null;
    public name: string|null;
    public hoverName: string|null;
    public description: string|null;
    public signal: string|null;
    public isContacting: boolean;
    public isLost: boolean;

    constructor() {
        this.key = null;
        this.name = null;
        this.hoverName = null;
        this.description = null;
        this.signal = null;
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
