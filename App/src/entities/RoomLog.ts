
export class RoomLog {
    public message : string|null;
    public visibility : string|null;
    public age : string|null;

    constructor() {
        this.message = null;
        this.visibility = null;
        this.age = null;
    }
    load(object: any): RoomLog {
        if (typeof object !== "undefined") {
            this.message = object.log;
            this.visibility = object.visibility;
            this.age = object.age;
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
            this.age = object.age;
        }

        return this;
    }
}
