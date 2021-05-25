export class Alert {
    constructor() {
        this.key = null;
        this.name = null;
        this.description = null;
    }
    load = function(object) {
        if (typeof object !== "undefined") {
            this.key = object.key;
            this.name = object.name;
            this.description = object.description;
        }
        return this;
    }
    jsonEncode = function() {
        return JSON.stringify(this);
    }
    decode = function(jsonString) {
        if (jsonString) {
            let object = JSON.parse(jsonString);
            this.key = object.key;
            this.name = object.name;
            this.description = object.description;
        }

        return this;
    }
}
