import { Character } from "@/entities/Character";

export class Message {
    constructor() {
        this.id = null;
        this.message = null;
        this.character = new Character();
        this.child = [];
        this.date = null;
    }

    load = function(object) {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.message = object.message;
            this.character = this.character.load(object.character);
            this.child = [];
            object.child.forEach((childMessageData) => {
                let childMessage = (new Message()).load(childMessageData);
                this.child.push(childMessage);
            });
            this.date = new Date(object.createdAt);
        }
        return this;
    }
    jsonEncode = function() {
        return JSON.stringify(this);
    }
    decode = function(jsonString) {
        if (jsonString) {
            let object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
