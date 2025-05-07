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
    public collectScrapNumber: Map<integer, integer>|null;
    public collectScrapPatrolShipDamage: Map<integer, integer>|null;
    public collectScrapPlayerDamage: Map<integer, integer>|null;
    public failedManoeuvreDaedalusDamage: Map<integer, integer>|null;
    public failedManoeuvrePatrolShipDamage: Map<integer, integer>|null;
    public failedManoeuvrePlayerDamage: Map<integer, integer>|null;

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
        this.collectScrapNumber = new Map();
        this.collectScrapPatrolShipDamage = new Map();
        this.collectScrapPlayerDamage = new Map();
        this.failedManoeuvreDaedalusDamage = new Map();
        this.failedManoeuvrePatrolShipDamage = new Map();
        this.failedManoeuvrePlayerDamage = new Map();
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

        this.addPatrolShipAttributes(object);

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

        this.encodePatrolShipAttributes(data);
        return data;
    }
    decode(jsonString : string): EquipmentConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }
        return this;
    }

    private addPatrolShipAttributes(object: any){
        if(!this.equipmentType?.includes("patrol_ship")) return;

        if (typeof object.collectScrapNumber !== 'undefined') {
            for (const [key, value] of Object.entries(object.collectScrapNumber)) {
                if (typeof key === 'string' && typeof value === 'number') {
                    this.collectScrapNumber?.set(Number(key), value);
                }
            }
        }
        if (typeof object.collectScrapPatrolShipDamage !== 'undefined') {
            for (const [key, value] of Object.entries(object.collectScrapPatrolShipDamage)) {
                if (typeof key === 'string' && typeof value === 'number') {
                    this.collectScrapPatrolShipDamage?.set(Number(key), value);
                }
            }
        }
        if (typeof object.collectScrapPlayerDamage !== 'undefined') {
            for (const [key, value] of Object.entries(object.collectScrapPlayerDamage)) {
                if (typeof key === 'string' && typeof value === 'number') {
                    this.collectScrapPlayerDamage?.set(Number(key), value);
                }
            }
        }
        if (typeof object.failedManoeuvreDaedalusDamage !== 'undefined') {
            for (const [key, value] of Object.entries(object.failedManoeuvreDaedalusDamage)) {
                if (typeof key === 'string' && typeof value === 'number') {
                    this.failedManoeuvreDaedalusDamage?.set(Number(key), value);
                }
            }
        }
        if (typeof object.failedManoeuvrePatrolShipDamage !== 'undefined') {
            for (const [key, value] of Object.entries(object.failedManoeuvrePatrolShipDamage)) {
                if (typeof key === 'string' && typeof value === 'number') {
                    this.failedManoeuvrePatrolShipDamage?.set(Number(key), value);
                }
            }
        }
        if (typeof object.failedManoeuvrePlayerDamage !== 'undefined') {
            for (const [key, value] of Object.entries(object.failedManoeuvrePlayerDamage)) {
                if (typeof key === 'string' && typeof value === 'number') {
                    this.failedManoeuvrePlayerDamage?.set(Number(key), value);
                }
            }
        }
    }

    private encodePatrolShipAttributes(data: any) {
        if(!this.equipmentType?.includes("patrol_ship")) return;

        const collectScrapNumber : object = {};
        this.collectScrapNumber?.forEach((value, key) => {
            // @ts-ignore
            collectScrapNumber[key] = value;
        });
        const collectScrapPatrolShipDamage : object = {};
        this.collectScrapPatrolShipDamage?.forEach((value, key) => {
            // @ts-ignore
            collectScrapPatrolShipDamage[key] = value;
        });
        const collectScrapPlayerDamage : object = {};
        this.collectScrapPlayerDamage?.forEach((value, key) => {
            // @ts-ignore
            collectScrapPlayerDamage[key] = value;
        });
        const failedManoeuvreDaedalusDamage : object = {};
        this.failedManoeuvreDaedalusDamage?.forEach((value, key) => {
            // @ts-ignore
            failedManoeuvreDaedalusDamage[key] = value;
        });
        const failedManoeuvrePlayerDamage : object = {};
        this.failedManoeuvrePlayerDamage?.forEach((value, key) => {
            // @ts-ignore
            failedManoeuvrePlayerDamage[key] = value;
        });
        const failedManoeuvrePatrolShipDamage : object = {};
        this.failedManoeuvrePatrolShipDamage?.forEach((value, key) => {
            // @ts-ignore
            failedManoeuvrePatrolShipDamage[key] = value;
        });

        data.collectScrapNumber = collectScrapNumber;
        data.collectScrapPatrolShipDamage = collectScrapPatrolShipDamage;
        data.collectScrapPlayerDamage = collectScrapPlayerDamage;
        data.failedManoeuvreDaedalusDamage = failedManoeuvreDaedalusDamage;
        data.failedManoeuvrePlayerDamage = failedManoeuvrePlayerDamage;
        data.failedManoeuvrePatrolShipDamage = failedManoeuvrePatrolShipDamage;
    }
}
