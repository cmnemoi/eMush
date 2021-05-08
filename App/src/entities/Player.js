import { Daedalus } from "@/entities/Daedalus";
import { Room } from "@/entities/Room";
import { Item } from "@/entities/Item";
import { Status } from "@/entities/Status";
import { Action } from "@/entities/Action";

export class Player {
    constructor() {
        this.id = null;
        this.gameStatus = null;
        this.characterKey = null;
        this.characterValue = null;
        this.actionPoint = null;
        this.movementPoint = null;
        this.healthPoint = null;
        this.moralPoint = null;
        this.triumph = null;
        this.gameStatus = null;
        this.daedalus = null;
        this.items = [];
        this.statuses = [];
        this.actions = [];
        this.room = null;
    }
    load = function(object) {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.gameStatus = object.gameStatus;
            this.characterKey = object.character['key'];
            this.characterValue = object.character['value'];
            this.actionPoint = object.actionPoint;
            this.movementPoint = object.movementPoint;
            this.healthPoint = object.healthPoint;
            this.moralPoint = object.moralPoint;
            this.triumph = object.triumph;
            this.gameStatus = object.gameStatus;
            if (typeof object.daedalus !== 'undefined') {
                this.daedalus = (new Daedalus()).load(object.daedalus);
            }
            if (typeof object.room !== 'undefined') {
                this.room = (new Room()).load(object.room);
            }
            if (typeof object.items !== 'undefined') {
                object.items.forEach((itemObject) => {
                    let item = (new Item).load(itemObject);
                    this.items.push(item);
                });
            }
            if (typeof object.actions !== 'undefined') {
                object.actions.forEach((actionObject) => {
                    let action = (new Action()).load(actionObject);
                    this.actions.push(action);
                });
            }
            if (typeof object.statuses !== 'undefined') {
                object.statuses.forEach((statusObject) => {
                    let status = (new Status()).load(statusObject);
                    this.statuses.push(status);
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
