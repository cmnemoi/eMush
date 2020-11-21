import {Daedalus} from "@/entities/Daedalus";

export class Player {
    constructor() {
        this.id = null;
        this.character_key = null;
        this.character_value = null;
        this.actionPoint = null;
        this.movementPoint = null;
        this.healthPoint = null;
        this.moralPoint = null;
        this.triumph = null;
        this.gameStatus = null;
        this.daedalus = null;
    }
    load = function(object) {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.character_key = object.character['key'];
            this.character_value = object.character['value'];
            this.actionPoint = object.actionPoint;
            this.movementPoint = object.movementPoint;
            this.healthPoint = object.healthPoint;
            this.moralPoint = object.moralPoint;
            this.triumph = object.triumph;
            this.gameStatus = object.gameStatus;
            this.daedalus = (new Daedalus()).load(object.daedalus)
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
            this.name = object.name;
            this.keyName = object.keyName;
            this.actionPoint = object.actionPoint;
            this.movementPoint = object.movementPoint;
            this.healthPoint = object.healthPoint;
            this.moralPoint = object.moralPoint;
            this.triumph = object.triumph;
            this.gameStatus = object.gameStatus;
        }

        return this;
    }
}