import { Character } from "./Character";

export class ChannelParticipant {
    constructor() {
        this.id = null;
        this.character = null;
        this.joinedAt = null;
    }

    load = function(object) {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.character = (new Character()).load(object.character);
            this.joinedAt = new Date(object.joinedAt);
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
