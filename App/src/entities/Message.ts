import { Character } from "@/entities/Character";

export class Message {
    public id : number|null
    public message : string|null
    public character : Character
    public child : Array<Message>
    public date : Date|null

    constructor() {
        this.id = null;
        this.message = null;
        this.character = new Character();
        this.child = [];
        this.date = null;
    }

    load(object: any) {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.message = object.message;
            this.character = this.character.load(object.character);
            this.child = [];
            object.child.forEach((childMessageData: any) => {
                let childMessage = (new Message()).load(childMessageData);
                this.child.push(childMessage);
            });
            this.date = new Date(object.createdAt);
        }
        return this;
    }
    jsonEncode() {
        return JSON.stringify(this);
    }
    decode(jsonString: string) {
        if (jsonString) {
            let object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
