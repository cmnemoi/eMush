import { ChannelParticipant } from "./ChannelParticipant";

export class Channel {
    constructor() {
        this.id = null;
        this.scope = null;
        this.name = null;
        this.description = null;
        this.participants = [];
    }

    load = function(object) {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.scope = object.scope;
            this.name = object.name;
            this.description = object.description;
            if (typeof object.participants !== 'undefined') {
                object.participants.forEach((itemObject) => {
                    let participant = (new ChannelParticipant()).load(itemObject);
                    this.participants.push(participant);
                });
            }
        }
        return this;
    }
    jsonEncode = function() {
        return JSON.stringify(this);
    }
    decode = function(jsonString) {
        if (jsonString) {
            let object = JSON.parse(jsonString);
            this.id = object.id;
            this.scope = object.scope;
            this.participants = object.participants;
        }

        return this;
    }

    getParticipant = function (key) {
        return this.participants.find(element => element.character.key === key);
    }
}
