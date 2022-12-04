import { GameConfig } from "@/entities/Config/GameConfig";

export class CharacterConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public initStatuses: string|null;
    public actions: Array<any>|null;
    public startingItems: Array<any>|null;
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
    public maxNumberPrivateChannel: number|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.initStatuses = null;
        this.actions = [];
        this.startingItems = [];
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
        this.maxNumberPrivateChannel = null;
    }
    load(object:any) : CharacterConfig {
        if (typeof object !== "undefined") {
            this.iri = object.iri;
            this.id = object.id;
            this.name = object.name;
            this.initStatuses = object.initStatuses;
            this.actions = object.actions;
            this.startingItems = object.startingItems;
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
            this.maxNumberPrivateChannel = object.maxNumberPrivateChannel;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id,
            'name': this.name,
            'initStatuses': this.initStatuses,
            'actions': this.actions,
            'startingItems': this.startingItems,
            'initHealthPoint': this.initHealthPoint,
            'maxHealthPoint': this.maxHealthPoint,
            'initMoralPoint': this.initMoralPoint,
            'maxMoralPoint': this.maxMoralPoint,
            'initSatietyPoint': this.initSatiety,
            'initActionPoint': this.initActionPoint,
            'maxActionPoint': this.maxActionPoint,
            'initMovementPoint': this.initMovementPoint,
            'maxMovementPoint': this.maxMovementPoint,
            'maxItemInInventory': this.maxItemInInventory,
            'maxNumberPrivateChannel': this.maxNumberPrivateChannel
        };
    }
    decode(jsonString : string): CharacterConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
            this.name = object.name;
            this.initStatuses = object.initStatuses;
            this.actions = object.actions;
            this.startingItems = object.startingItems;
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
            this.maxNumberPrivateChannel = object.maxNumberPrivateChannel;
        }

        return this;
    }
}
