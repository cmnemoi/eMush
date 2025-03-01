export class XylophEntry {
    public key!: string;
    public name!: string;
    public description!: string;
    public isDecoded!: boolean;

    public load(object: any): XylophEntry {
        if (object) {
            this.key = object.key;
            this.name = object.name;
            this.description = object.description;
            this.isDecoded = object.isDecoded;
        }
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: string): XylophEntry {
        return JSON.parse(jsonString);
    }
}
