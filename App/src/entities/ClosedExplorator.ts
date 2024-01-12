export class ClosedExplorator {
    public id!: integer;
    public logName!: string;
    public isAlive!: boolean;

    public load(object: any): ClosedExplorator {
        if (object) {
            this.id = object.id;
            this.logName = object.logName;
            this.isAlive = object.isAlive;
        }

        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this, null, 4);
    }

    public decode(jsonString : string): ClosedExplorator {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }

}
