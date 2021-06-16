export class Daedalus {
    constructor() {
        this.id = null;
        this.day = null;
        this.cycle = null;
        this.oxygen = null;
        this.fuel = null;
        this.hull = null;
        this.shield = null;
        this.currentCycle = null;
        this.nextCycle = null;
        this.nextCycle = 0;
        this.cryogenizedPlayers = 0;
        this.humanPlayerAlive = 0;
        this.humanPlayerDead = 0;
        this.mushPlayerAlive = 0;
        this.mushPlayerDead = 0;
        this.crewPlayer = 0;
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
            this.currentCycle = object.currentCycle;
            this.nextCycle = new Date(object.nextCycle);
            this.cryogenizedPlayers = object.cryogenizedPlayers;
            this.humanPlayerAlive = object.humanPlayerAlive;
            this.humanPlayerDead = object.humanPlayerDead;
            this.mushPlayerAlive = object.mushPlayerAlive;
            this.mushPlayerDead = object.mushPlayerDead;
            this.crewPlayer = object.crewPlayer;
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
