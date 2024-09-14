export class RoomLog {
    public id!: integer;
    public message : string|null;
    public visibility : string|null;
    public date : string|null;
    public place: string|null;
    public day: integer|null;
    public cycle: integer|null;
    public isUnread!: boolean;

    constructor() {
        this.message = null;
        this.visibility = null;
        this.date = null;
        this.place = null;
        this.day = null;
        this.cycle = null;
    }
    load(object: any): RoomLog {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.message = object.log;
            this.visibility = object.visibility;
            this.date = object.date;
            this.place = object.place;
            this.day = object.day;
            this.cycle = object.cycle;
            this.isUnread = object.isUnread;
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: string): RoomLog {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
    read(): void {
        this.isUnread = false;
    }
}
