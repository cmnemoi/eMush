export class ContactablePlayer {
    key!: string;
    name!: string;

    public load(object: any): ContactablePlayer {
        if (object) {
            this.key = object.key;
            this.name = object.name;
        }

        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString : string): ContactablePlayer {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
