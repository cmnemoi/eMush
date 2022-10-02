import { GameConfig } from "@/entities/Config/GameConfig";

export class EquipmentConfig {
    public iri: string|null;
    public id: number|null;
    public gameConfig: GameConfig|null;
    public name: string|null;
    public initStatus: string|null;
    public mechanics: Array<any>|null;
    public isFireDestroyable: boolean|null;
    public isFireBreakable: boolean|null;
    public isBreakable: boolean|null;
    public actions: Array<any>|null;
    public dismountedProducts: Array<any>|null;
    public isPersonal: boolean|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.gameConfig = null;
        this.name = null;
        this.initStatus = null;
        this.mechanics = [];
        this.isFireDestroyable = null;
        this.isFireBreakable = null;
        this.isBreakable = null;
        this.actions = [];
        this.dismountedProducts = [];
        this.isPersonal = null;
    }
    load(object:any) : EquipmentConfig {
        if (typeof object !== "undefined") {
            this.iri = object.iri;
            this.id = object.id;
            this.gameConfig = object.gameConfig;
            this.name = object.name;
            this.initStatus = object.initStatus;
            this.mechanics = object.mechanics;
            this.isFireDestroyable = object.isFireDestroyable;
            this.isFireBreakable = object.isFireBreakable;
            this.isBreakable = object.isBreakable;
            this.actions = object.actions;
            this.dismountedProducts = object.dismountedProducts;
            this.isPersonal = object.isPersonal;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id,
            'gameConfig': this.gameConfig?.iri,
            'name': this.name,
            'initStatus': this.initStatus,
            'mechanics': this.mechanics,
            'isFireDestroyable': this.isFireDestroyable,
            'isFireBreakable': this.isFireBreakable,
            'isBreakable': this.isBreakable,
            'actions': this.actions,
            'dismountedProducts': this.dismountedProducts,
            'isPersonal': this.isPersonal
        };
    }
    decode(jsonString : string): EquipmentConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
            this.gameConfig = object.gameConfig;
            this.name = object.name;
            this.initStatus = object.initStatus;
            this.mechanics = object.mechanics;
            this.isFireDestroyable = object.isFireDestroyable;
            this.isFireBreakable = object.isFireBreakable;
            this.isBreakable = object.isBreakable;
            this.actions = object.actions;
            this.dismountedProducts = object.dismountedProducts;
            this.isPersonal = object.isPersonal;
        }

        return this;
    }
}
