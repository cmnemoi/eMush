import {Daedalus} from "@/entities/Daedalus";
import {Room} from "@/entities/Room";

export class Player {
    constructor() {
        this.id = null;
        this.characterKey = null;
        this.characterValue = null;
        this.actionPoint = null;
        this.movementPoint = null;
        this.healthPoint = null;
        this.moralPoint = null;
        this.triumph = null;
        this.gameStatus = null;
        this.daedalus = null;
        this.room = null;
    }
    load = function(object) {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.characterKey = object.character['key'];
            this.characterValue = object.character['value'];
            this.actionPoint = object.actionPoint;
            this.movementPoint = object.movementPoint;
            this.healthPoint = object.healthPoint;
            this.moralPoint = object.moralPoint;
            this.triumph = object.triumph;
            this.gameStatus = object.gameStatus;
            this.daedalus = (new Daedalus()).load(object.daedalus)
            this.room = (new Room()).load(object.room)
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