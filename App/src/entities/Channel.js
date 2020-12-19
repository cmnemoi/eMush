export class Channel {
    constructor() {
        this.id = null;
        this.scope = null;
        this.participants = [];
        this.messages = [];
    }

    load = function(object) {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.scope = object.scope;
            this.participants = object.participants;
        }
        return this;
    }
    jsonEncode = function() {
        return JSON.stringify(this);
    }
    decode = function(jsonString) {
        if (jsonString) {
            let object = JSON.parse(jsonString)
            this.id = object.id;
            this.scope = object.scope;
            this.participants = object.participants;
        }

        return this;
    }
}