export class Character {
    public key: string|null
    public name: string|null

    constructor() {
        this.key = null;
        this.name = null;
    }

    load(object: any): Character {
        if (typeof object !== "undefined") {
            this.key = object.key;
            this.name = object.value;
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: string): Character {
        if (jsonString) {
            let object = JSON.parse(jsonString);
            this.key = object.key;
            this.name = object.value;
        }

        return this;
    }
}
