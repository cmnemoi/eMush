export class ContactablePlayer {
    id!: number;
    key!: string;
    name!: string;

    public load(object: any): ContactablePlayer {
        if (object) {
            this.id = object.id;
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
