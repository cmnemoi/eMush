import { Character } from "@/entities/Character";

export class Message {
    public id : number|null;
    public message : string|null;
    public character : Character;
    public child : Array<Message>;
    public date : string|null;
    public hidden : boolean = true;

    constructor() {
        this.id = null;
        this.message = null;
        this.character = new Character();
        this.child = [];
        this.date = null;
    }

    load(object: any): Message {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.message = object.message;
            this.character = this.character.load(object.character);
            this.child = [];
            object.child.forEach((childMessageData: any) => {
                const childMessage = (new Message()).load(childMessageData);
                this.child.push(childMessage);
            });
            this.date = object.date;
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: string): Message {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
    hasChildren(): boolean {
        return this.child.length > 0;
    }
}
