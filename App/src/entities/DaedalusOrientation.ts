export class DaedalusOrientation {
    public key!: string;
    public name!: string;

    public load(object: any): DaedalusOrientation {
        if (object !== null && object !== undefined) {
            this.key = object.key;
            this.name = object.name;
        }
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: any): DaedalusOrientation {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}