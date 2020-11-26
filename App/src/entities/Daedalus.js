export class Daedalus {
    constructor() {
        this.id = null;
        this.day = null;
        this.cycle = null;
        this.oxygen = null;
        this.fuel = null;
        this.hull = null;
        this.shield = null;
        this.nextCycle = null;
    }
    load = function(object) {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.day = object.day;
            this.cycle = object.cycle;
            this.oxygen = object.oxygen;
            this.fuel = object.fuel;
            this.hull = object.hull;
            this.shield = object.shield;
            this.nextCycle = new Date(object.nextCycle);
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
            this.day = object.day;
            this.cycle = object.cycle;
            this.oxygen = object.oxygen;
            this.fuel = object.fuel;
            this.hull = object.hull;
            this.shield = object.shield;
        }

        return this;
    }
}