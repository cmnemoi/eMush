export class DeadPlayerInfo {
    constructor() {
        this.id = null;
        this.characterKey = null;
        this.characterValue = null;
        this.endCauseKey = null;
        this.endCauseValue = null;
        this.endCauseDescription= null;
        this.players = []
    }

    load = function(object) {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.deathTime = object.deathTime;

            if (typeof object.endCause !== "undefined") {
                this.endCauseKey = object.endCause['key'];
                this.endCauseValue = object.endCause['name'];
                this.endCauseDescription = object.endCause['description'];

            }

            if (typeof object.character !== "undefined") {
                this.characterKey = object.character['key'];
                this.characterValue = object.character['value'];
            }

            if (typeof object.players !== 'undefined') {
                object.players.forEach((deadPlayerObject) => {
                    let deadPlayer = (new DeadPlayerInfo()).load(deadPlayerObject);
                    this.players.push(deadPlayer);
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
            this.load(object);
        }

        return this;
    }
}
