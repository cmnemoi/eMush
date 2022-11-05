
export class RoomLog {
    public message : string|null;
    public visibility : string|null;
    public date : string|null;

    constructor() {
        this.message = null;
        this.visibility = null;
        this.date = null;
    }
    load(object: any): RoomLog {
        if (typeof object !== "undefined") {
            this.message = object.log;
            this.visibility = object.visibility;
            this.date = object.date;
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: string): RoomLog {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.message = object.key;
            this.visibility = object.visibility;
            this.date = object.date;
        }

        return this;
    }
}
