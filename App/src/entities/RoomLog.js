export class RoomLog {
    constructor() {
        this.message = null;
        this.visibility = null;
        this.date = null;
    }
    load = function(object) {
        if (typeof object !== "undefined") {
            this.message = object.log;
            this.visibility = object.visibility;
            this.date = new Date(object.date);
        }
        return this;
    }
    jsonEncode = function() {
        return JSON.stringify(this);
    }
    decode = function(jsonString) {
        if (jsonString) {
            let object = JSON.parse(jsonString);
            this.message = object.key;
            this.visibility = object.visibility;
            this.date = new Date(object.date);
        }

        return this;
    }
}
