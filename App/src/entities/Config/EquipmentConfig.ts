import { Action } from "@/entities/Action";
import { StatusConfig } from "@/entities/Config/StatusConfig";
import { Mechanics } from "@/entities/Config/Mechanics";

export class EquipmentConfig {
    public iri: string|null;
    public id: number|null;
    public equipmentType: string|null;
    public name: string|null;
    public equipmentName: string|null;
    public initStatuses: StatusConfig[]|null;
    public mechanics: Mechanics[]|null;
    public breakableType: string|null;
    public actions: Action[]|null;
    public dismountedProducts: Map<string, number>|null;
    public isPersonal: boolean|null;
    public isStackable: boolean|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.equipmentType = null;
        this.name = null;
        this.equipmentName = null;
        this.initStatuses = null;
        this.mechanics = null;
        this.breakableType = null;
        this.actions = null;
        this.dismountedProducts = new Map();
        this.isPersonal = null;
        this.isStackable = null;
    }
    load(object:any) : EquipmentConfig {
        if (typeof object !== "undefined") {
            this.iri = object["@id"];
            this.id = object.id;
            this.name = object.name;
            this.equipmentName = object.equipmentName;
            this.breakableType = object.breakableType;
            this.isPersonal = object.isPersonal;
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
                object.initStatuses.forEach((statusData: any) => {
                    const status = (new StatusConfig()).load(statusData);
                    initStatuses.push(status);
                });
                this.initStatuses = initStatuses;
            }
            if (typeof object.mechanics !== 'undefined') {
                const mechanics : Mechanics[] = [];
                object.mechanics.forEach((mechanicsData: any) => {
                    const mechanic = (new Mechanics()).load(mechanicsData);
                    mechanics.push(mechanic);
                });
                this.mechanics = mechanics;
            }
            if (typeof object.dismountedProducts !== 'undefined') {
                for (const [key, value] of Object.entries(object.dismountedProducts)) {
                    if (typeof key === 'string' && typeof value === 'number') {
                        this.dismountedProducts?.set(key, value);
                    }
                }
            }
            this.equipmentType = object['@type'];
            if (this.equipmentType === 'ItemConfig') {
                this.isStackable = object.isStackable;
            }
        }
        return this;
    }
    jsonEncode() : object {
        const actions : string[] = [];
        this.actions?.forEach(action => (typeof action.iri === 'string' ? actions.push(action.iri) : null));
        const initStatuses : string[] = [];
        this.initStatuses?.forEach(status => (typeof status.iri === 'string' ? initStatuses.push(status.iri) : null));
        const mechanics : string[] = [];
        this.mechanics?.forEach(mechanic => (typeof mechanic.iri === 'string' ? mechanics.push(mechanic.iri) : null));
        // api-platform doesn't support Map so we need to convert it to object
        // TODO: could we work with an object instead of a Map from the beginning?
        const dismountedProducts : object = {};
        this.dismountedProducts?.forEach((value, key) => {
            // @ts-ignore
            dismountedProducts[key] = value;
        });
        const data : any = {
            'id': this.id,
            'name': this.name,
            'equipmentName': this.equipmentName,
            'initStatuses': initStatuses,
            'mechanics': mechanics,
            'breakableType': this.breakableType,
            'actions': actions,
            'dismountedProducts': dismountedProducts,
            'isPersonal': this.isPersonal
        };
        if (this.equipmentType === 'ItemConfig') {
            data.isStackable = this.isStackable;
        }
        return data;
    }
    decode(jsonString : string): EquipmentConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }
        return this;
    }
}
