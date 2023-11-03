import { Action } from '@/entities/Action';
import { ModifierConfig } from '@/entities/Config/ModifierConfig';

export class Mechanics {
    public iri: string|null;
    public id: number|null;
    public mechanicsType: string|null;
    public name: string|null;
    public mechanics: Array<string>|null;
    public actions: Action[]|null;
    public equipment: string|null;
    public ingredients: Map<string, number>|null;
    public skill: string|null;
    public content: string|null;
    public isTranslated: boolean|null;
    public canShred: boolean|null;
    public isPerishable: boolean|null;
    public plantName: string|null;
    public modifierConfigs: ModifierConfig[]|null;
    public fruit: string|null;
    public maturationTime: Map<integer, integer>|null;
    public oxygen: Map<integer, integer>|null;
    public healthPoints: Map<integer, integer>|null;
    public moralPoints: Map<integer, integer>|null;
    public actionPoints: Map<integer, integer>|null;
    public movementPoints: Map<integer, integer>|null;
    public satiety: number|null;
    public extraEffects: Map<string, number>|null;
    public baseAccuracy: number|null;
    public baseDamageRange: Map<integer, integer>|null;
    public expeditionBonus: number|null;
    public criticalSuccessRate: number|null;
    public criticalFailRate: number|null;
    public oneShotRate: number|null;
    public collectScrapNumber: Map<integer, integer>|null;
    public collectScrapPatrolShipDamage: Map<integer, integer>|null;
    public collectScrapPlayerDamage: Map<integer, integer>|null;
    public dockingPlace: string|null;
    public failedManoeuvreDaedalusDamage: Map<integer, integer>|null;
    public failedManoeuvrePatrolShipDamage: Map<integer, integer>|null;
    public failedManoeuvrePlayerDamage: Map<integer, integer>|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.mechanicsType = null;
        this.name = null;
        this.mechanics = null;
        this.actions = null;
        this.equipment = null;
        this.ingredients = new Map();
        this.skill = null;
        this.content = null;
        this.isTranslated = null;
        this.canShred = null;
        this.isPerishable = null;
        this.plantName = null;
        this.modifierConfigs = null;
        this.fruit = null;
        this.maturationTime = new Map();
        this.oxygen = new Map();
        this.healthPoints = new Map();
        this.moralPoints = new Map();
        this.actionPoints = new Map();
        this.movementPoints = new Map();
        this.healthPoints = new Map();
        this.satiety = null;
        this.extraEffects = new Map();
        this.baseAccuracy = null;
        this.baseDamageRange = new Map();
        this.expeditionBonus = null;
        this.criticalSuccessRate = null;
        this.criticalFailRate = null;
        this.oneShotRate = null;
        this.collectScrapNumber = new Map();
        this.collectScrapPatrolShipDamage = new Map();
        this.collectScrapPlayerDamage = new Map();
        this.dockingPlace = null;
        this.failedManoeuvreDaedalusDamage = new Map();
        this.failedManoeuvrePatrolShipDamage = new Map();
        this.failedManoeuvrePlayerDamage = new Map();
    }

    load(object:any) : Mechanics {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.mechanicsType = object['@type'];
            this.name = object.name;
            this.mechanics = object.mechanics;
        }

        if (typeof object.actions !== 'undefined') {
            const actions : Action[] = [];
            object.actions.forEach((actionData: any) => {
                const action = (new Action()).load(actionData);
                actions.push(action);
            });
            this.actions = actions;
        }

        this.addBlueprintAttributes(object);
        this.addBookAttributes(object);
        this.addDocumentAttributes(object);
        this.addFruitAttributes(object);
        this.addGearAttributes(object);
        this.addPlantAttributes(object);
        this.addRationAttributes(object);
        this.addWeaponAttributes(object);
        this.addPatrolShipAttributes(object);

        return this;
    }

    jsonEncode() : object {
        const actions : string[] = [];
        this.actions?.forEach(action => (typeof action.iri === 'string' ? actions.push(action.iri) : null));

        const data: any = {
            'id': this.id,
            'name': this.name,
            'mechanics': this.mechanics,
            'actions': actions
        };

        this.encodeBlueprintAttributes(data);
        this.encodeBookAttributes(data);
        this.encodeDocumentAttributes(data);
        this.encodeFruitAttributes(data);
        this.encodeGearAttributes(data);
        this.encodePlantAttributes(data);
        this.encodeRationAttributes(data);
        this.encodeWeaponAttributes(data);
        this.encodePatrolShipAttributes(data);

        return data;
    }

    decode(jsonString : string): Mechanics {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }

    private addBlueprintAttributes(object: any){
        if(!this.mechanics?.includes("blueprint")) return;

        if (typeof object.ingredients !== 'undefined') {
            for (const [key, value] of Object.entries(object.ingredients)) {
                if (typeof key === 'string' && typeof value === 'number') {
                    this.ingredients?.set(key, value);
                }
            }
        }
        this.equipment = object.craftedEquipmentName;
    }

    private encodeBlueprintAttributes(data: any){
        if(!this.mechanics?.includes("blueprint")) return;

        const ingredients : object = {};
        this.ingredients?.forEach((value, key) => {
            // @ts-ignore
            ingredients[key] = value;
        });

        data.equipment = this.equipment;
        data.ingredients = ingredients;
    }

    private encodeBookAttributes(data: any){
        if(!this.mechanics?.includes("book")) return;

        data.skill = this.skill;
    }

    private addBookAttributes(object: any){
        if(!this.mechanics?.includes("book")) return;

        this.skill = object.skill;
    }

    private addDocumentAttributes(object: any){
        if(!this.mechanics?.includes("document")) return;

        this.content = object.content;
        this.isTranslated = object.isTranslated;
        this.canShred = object.canShred;
    }

    private encodeDocumentAttributes(data: any){
        if(!this.mechanics?.includes("document")) return;

        data.content = this.content;
        data.isTranslated = this.isTranslated;
        data.canShred = this.canShred;
    }

    private addFruitAttributes(object: any){
        if(!this.mechanics?.includes("fruit"))return;

        this.plantName = object.plantName;
    }

    private encodeFruitAttributes(data: any){
        if(!this.mechanics?.includes("fruit"))return;

        data.plantName = this.plantName;
    }

    private addGearAttributes(object: any){
        if(!this.mechanics?.includes("gear")) return;

        this.modifierConfigs = [];
        object.modifierConfigs.forEach((modifierConfigData: any) => {
            const modifierConfig = (new ModifierConfig()).load(modifierConfigData);
            this.modifierConfigs?.push(modifierConfig);
        });
    }

    private encodeGearAttributes(data: any){
        if(!this.mechanics?.includes("gear")) return;

        const modifierConfigs : string[] = [];
        this.modifierConfigs?.forEach(modifierConfig => (typeof modifierConfig.iri === 'string' ? modifierConfigs.push(modifierConfig.iri) : null));
        data.modifierConfigs = modifierConfigs;
    }

    private addPlantAttributes(object: any){
        if(!this.mechanics?.includes("plant"))return;

        this.fruit = object.fruitName;

        if (typeof object.maturationTime !== 'undefined') {
            for (const [key, value] of Object.entries(object.maturationTime)) {
                if (typeof key === 'string' && typeof value === 'number') {
                    this.maturationTime?.set(Number(key), value);
                }
            }
        }

        if (typeof object.oxygen !== 'undefined') {
            for (const [key, value] of Object.entries(object.oxygen)) {
                if (typeof key === 'string' && typeof value === 'number') {
                    this.oxygen?.set(Number(key), value);
                }
            }
        }
    }

    private encodePlantAttributes(data: any){
        if(!this.mechanics?.includes("plant"))return;

        const maturationTime : object = {};
        this.maturationTime?.forEach((value, key) => {
            // @ts-ignore
            maturationTime[key] = value;
        });

        const healthPoints : object = {};
        this.healthPoints?.forEach((value, key) => {
            // @ts-ignore
            healthPoints[key] = value;
        });

        data.fruit = this.fruit;
        data.maturationTime = maturationTime;
        data.healthPoints = healthPoints;
    }

    private addRationAttributes(object: any){
        if(!this.mechanics?.includes("ration")) return;

        this.isPerishable = object.isPerishable;
        this.satiety = object.satiety;
        if (typeof object.moralPoints !== 'undefined') {
            for (const [key, value] of Object.entries(object.moralPoints)) {
                if (typeof key === 'string' && typeof value === 'number') {
                    this.moralPoints?.set(Number(key), value);
                }
            }
        }
        if (typeof object.actionPoints !== 'undefined') {
            for (const [key, value] of Object.entries(object.actionPoints)) {
                if (typeof key === 'string' && typeof value === 'number') {
                    this.actionPoints?.set(Number(key), value);
                }
            }
        }
        if (typeof object.movementPoints !== 'undefined') {
            for (const [key, value] of Object.entries(object.movementPoints)) {
                if (typeof key === 'string' && typeof value === 'number') {
                    this.movementPoints?.set(Number(key), value);
                }
            }
        }
        if (typeof object.healthPoints !== 'undefined') {
            for (const [key, value] of Object.entries(object.healthPoints)) {
                if (typeof key === 'string' && typeof value === 'number') {
                    this.healthPoints?.set(Number(key), value);
                }
            }
        }
        if (typeof object.extraEffects !== 'undefined') {
            for (const [key, value] of Object.entries(object.extraEffects)) {
                if (typeof key === 'string' && typeof value === 'number') {
                    this.extraEffects?.set(key, value);
                }
            }
        }
    }

    private encodeRationAttributes(data: any){
        if(!this.mechanics?.includes("ration")) return;

        const moralPoints : object = {};
        this.moralPoints?.forEach((value, key) => {
            // @ts-ignore
            moralPoints[key] = value;
        });

        const actionPoints : object = {};
        this.actionPoints?.forEach((value, key) => {
            // @ts-ignore
            actionPoints[key] = value;
        });

        const movementPoints : object = {};
        this.movementPoints?.forEach((value, key) => {
            // @ts-ignore
            movementPoints[key] = value;
        });

        const healthPoints : object = {};
        this.healthPoints?.forEach((value, key) => {
            // @ts-ignore
            healthPoints[key] = value;
        });

        const extraEffects : object = {};
        this.extraEffects?.forEach((value, key) => {
            // @ts-ignore
            extraEffects[key] = value;
        });

        data.isPerishable = this.isPerishable;
        data.satiety = this.satiety;
        data.moralPoints = moralPoints;
        data.actionPoints = actionPoints;
        data.movementPoints = movementPoints;
        data.healthPoints = healthPoints;
        data.extraEffects = extraEffects;
    }

    private addWeaponAttributes(object: any){
        if(!this.mechanics?.includes("weapon")) return;

        this.baseAccuracy = object.baseAccuracy;
        this.criticalSuccessRate = object.criticalSuccessRate;
        this.criticalFailRate = object.criticalFailRate;
        this.oneShotRate = object.oneShotRate;
        this.expeditionBonus = object.expeditionBonus;

        if (typeof object.baseDamageRange !== 'undefined') {
            for (const [key, value] of Object.entries(object.baseDamageRange)) {
                if (typeof key === 'string' && typeof value === 'number') {
                    this.baseDamageRange?.set(Number(key), value);
                }
            }
        }
    }

    private encodeWeaponAttributes(data: any){
        if(!this.mechanics?.includes("weapon")) return;

        const baseDamageRange : object = {};
        this.baseDamageRange?.forEach((value, key) => {
            // @ts-ignore
            baseDamageRange[key] = value;
        });

        data.baseAccuracy = this.baseAccuracy;
        data.criticalSuccessRate = this.criticalSuccessRate;
        data.criticalFailRate = this.criticalFailRate;
        data.oneShotRate = this.oneShotRate;
        data.expeditionBonus = this.expeditionBonus;
        data.baseDamageRange = baseDamageRange;
    }

    private addPatrolShipAttributes(object: any){
        if(!this.mechanics?.includes("patrol_ship")) return;

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

        this.dockingPlace = object.dockingPlace;
    }

    private encodePatrolShipAttributes(data: any) {
        if(!this.mechanics?.includes("patrolShip")) return;

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
        data.dockingPlace = this.dockingPlace;
    }

}
