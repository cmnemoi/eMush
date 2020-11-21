export class User {
    constructor() {
        this.id = null;
        this.username = null;
        this.currentGame = null;
    }
    load = function(object) {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.username = object.username;
            this.currentGame = object.currentGame;
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
            this.username = object.username;
            this.currentGame = object.currentGame;
        }

        return this;
    }
}