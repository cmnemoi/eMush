import { Action } from "@/entities/Action";

export class ItemConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public initStatus: string|null;
    public mechanics: Array<any>|null;
    public isFireDestroyable: boolean|null;
    public isFireBreakable: boolean|null;
    public isBreakable: boolean|null;
    public actions: Action[]|null;
    public dismountedProducts: Array<any>|null;
    public isPersonal: boolean|null;
    public isStackable: boolean|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.initStatus = null;
        this.mechanics = [];
        this.isFireDestroyable = null;
        this.isFireBreakable = null;
        this.isBreakable = null;
        this.actions = null;
        this.dismountedProducts = [];
        this.isPersonal = null;
        this.isStackable = null;
    }
    load(object:any) : ItemConfig {
        if (typeof object !== "undefined") {
            this.iri = object["@id"];
            this.id = object.id;
            this.name = object.name;
            this.initStatus = object.initStatus;
            this.mechanics = object.mechanics;
            this.isFireDestroyable = object.isFireDestroyable;
            this.isFireBreakable = object.isFireBreakable;
            this.isBreakable = object.isBreakable;
            this.dismountedProducts = object.dismountedProducts;
            this.isPersonal = object.isPersonal;
            this.isStackable = object.isStackable;
            if (typeof object.actions !== 'undefined') {
                const actions : Action[] = [];
                object.actions.forEach((actionData: any) => {
                    const action = (new Action()).load(actionData);
                    actions.push(action);
                });
                this.actions = actions;
            }
        }
        return this;
    }
    jsonEncode() : object {
        const actions : string[] = [];
        this.actions?.forEach(action => (typeof action.iri === 'string' ? actions.push(action.iri) : null));
        return {
            'id': this.id,
            'name': this.name,
            'initStatus': this.initStatus,
            'mechanics': this.mechanics,
            'isFireDestroyable': this.isFireDestroyable,
            'isFireBreakable': this.isFireBreakable,
            'isBreakable': this.isBreakable,
            'actions': actions,
            'dismountedProducts': this.dismountedProducts,
            'isPersonal': this.isPersonal,
            'isStackable': this.isStackable,

        };
    }
    decode(jsonString : string): ItemConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
