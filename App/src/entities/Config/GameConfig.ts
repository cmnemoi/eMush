export class GameConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public nbMush: number|null;
    public cyclePerGameDay: number|null;
    public cycleLength: number|null;
    public timeZone: string|null;
    public maxNumberPrivateChannel: number|null;
    public language: string|null;
    public initHealthPoint: number|null;
    public maxHealthPoint: number|null;
    public initMoralPoint: number|null;
    public maxMoralPoint: number|null;
    public initSatiety: number|null;
    public initActionPoint: number|null;
    public maxActionPoint: number|null;
    public initMovementPoint: number|null;
    public maxMovementPoint: number|null;
    public maxItemInInventory: number|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.nbMush = null;
        this.cyclePerGameDay = null;
        this.cycleLength = null;
        this.timeZone = null;
        this.maxNumberPrivateChannel = null;
        this.language = null;
        this.initHealthPoint = null;
        this.maxHealthPoint = null;
        this.initMoralPoint = null;
        this.maxMoralPoint = null;
        this.initSatiety = null;
        this.initActionPoint = null;
        this.maxActionPoint = null;
        this.initMovementPoint = null;
        this.maxMovementPoint = null;
        this.maxItemInInventory = null;
    }
    load(object:any) : GameConfig {
        if (typeof object !== "undefined") {
            this.iri = object.iri;
            this.id = object.id;
            this.name = object.name;
            this.nbMush = object.nbMush;
            this.cyclePerGameDay = object.cyclePerGameDay;
            this.cycleLength = object.cycleLength;
            this.timeZone = object.timeZone;
            this.maxNumberPrivateChannel = object.maxNumberPrivateChannel;
            this.language = object.language;
            this.initHealthPoint = object.initHealthPoint;
            this.maxHealthPoint = object.maxHealthPoint;
            this.initMoralPoint = object.initMoralPoint;
            this.maxMoralPoint = object.maxMoralPoint;
            this.initSatiety = object.initSatiety;
            this.initActionPoint = object.initActionPoint;
            this.maxActionPoint = object.maxActionPoint;
            this.initMovementPoint = object.initMovementPoint;
            this.maxMovementPoint = object.maxMovementPoint;
            this.maxItemInInventory = object.maxItemInInventory;
        }
        return this;
    }
    jsonEncode() : string {
        return JSON.stringify(this);
    }
    decode(jsonString : string): GameConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
            this.name = object.name;
            this.nbMush = object.nbMush;
            this.cyclePerGameDay = object.cyclePerGameDay;
            this.cycleLength = object.cycleLength;
            this.timeZone = object.timeZone;
            this.maxNumberPrivateChannel = object.maxNumberPrivateChannel;
            this.language = object.language;
            this.initHealthPoint = object.initHealthPoint;
            this.maxHealthPoint = object.maxHealthPoint;
            this.initMoralPoint = object.initMoralPoint;
            this.maxMoralPoint = object.maxMoralPoint;
            this.initSatiety = object.initSatiety;
            this.initActionPoint = object.initActionPoint;
            this.maxActionPoint = object.maxActionPoint;
            this.initMovementPoint = object.initMovementPoint;
            this.maxMovementPoint = object.maxMovementPoint;
            this.maxItemInInventory = object.maxItemInInventory;
        }

        return this;
    }
}
