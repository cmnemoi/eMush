import { Action } from "@/entities/Action";
import { StatusConfig } from "@/entities/Config/StatusConfig";
import { ItemConfig } from "@/entities/Config/ItemConfig";
import { DiseaseConfig } from "./DiseaseConfig";


export class CharacterConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public characterName: string|null;
    public initStatuses: StatusConfig[]|null;
    public actions: Action[]|null;
    public skills: Array<string>|null;
    public startingItems: ItemConfig[]|null;
    public initDiseases: DiseaseConfig[]|null;
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
        this.characterName = null;
        this.initStatuses = null;
        this.actions = null;
        this.skills = [];
        this.startingItems = null;
        this.initDiseases = null;
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
            this.characterName = object.characterName;
            this.skills = object.skills;
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
            if (typeof object.actions !== 'undefined') {
                const actions : Action[] = [];
                object.actions.forEach((actionData: any) => {
                    const action = (new Action()).load(actionData);
                    actions.push(action);
                });
                this.actions = actions;
            }
            if (typeof object.initStatuses !== 'undefined') {
                const initStatuses : StatusConfig[] = [];
                object.initStatuses.forEach((initStatusData: any) => {
                    const statusConfig = (new StatusConfig()).load(initStatusData);
                    initStatuses.push(statusConfig);
                });
                this.initStatuses = initStatuses;
            }
            if (typeof object.startingItems !== 'undefined') {
                const startingItems : ItemConfig[] = [];
                object.startingItems.forEach((startingItemData: any) => {
                    const itemConfig = (new ItemConfig()).load(startingItemData);
                    startingItems.push(itemConfig);
                });
                this.startingItems = startingItems;
            }
            if (typeof object.initDiseases !== 'undefined') {
                const initDiseases : DiseaseConfig[] = [];
                object.initDiseases.forEach((initDiseaseData: any) => {
                    const diseaseConfig = (new DiseaseConfig()).load(initDiseaseData);
                    initDiseases.push(diseaseConfig);
                });
                this.initDiseases = initDiseases;
            }
        }
        return this;
    }
    jsonEncode() : any {
        const actions : string[] = [];
        this.actions?.forEach(action => (typeof action.iri === 'string' ? actions.push(action.iri) : null));
        const initStatuses : string[] = [];
        this.initStatuses?.forEach(statusConfig => (typeof statusConfig.iri === 'string' ? initStatuses.push(statusConfig.iri) : null));
        const startingItems : string[] = [];
        this.startingItems?.forEach(itemConfig => (typeof itemConfig.iri === 'string' ? startingItems.push(itemConfig.iri) : null));
        const initDiseases : string[] = [];
        this.initDiseases?.forEach(diseaseConfig => (typeof diseaseConfig.iri === 'string' ? initDiseases.push(diseaseConfig.iri) : null));
        const data: any = {
            'id': this.id,
            'name': this.name,
            'characterName': this.characterName,
            'initStatuses': initStatuses,
            'actions': actions,
            'skills': this.skills,
            'startingItems': startingItems,
            'initDiseases': initDiseases,
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
        return data;
    }
    decode(jsonString : string): CharacterConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
