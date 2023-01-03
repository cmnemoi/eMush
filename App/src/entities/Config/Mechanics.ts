import { Action } from '@/entities/Action';
import { EquipmentConfig } from '@/entities/Config/EquipmentConfig';

export class Mechanics {
    public iri: string|null;
    public id: number|null;
    public mechanicsType: string|null;
    public name: string|null;
    public mechanics: Array<string>|null;
    public actions: Action[]|null;
    public equipment: EquipmentConfig|null;
    public ingredients: Map<string, number>|null;
    public skill: string|null;
    public content: string|null;
    public isTranslated: boolean|null;
    public canShred: boolean|null;

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
        this.addSkillAttributes(object);
        this.addDocumentAttributes(object);
        
        return this;
    }

    jsonEncode() : object {
        const actions : string[] = [];
        this.actions?.forEach(action => (typeof action.iri === 'string' ? actions.push(action.iri) : null));
        
        const data: any = {
            'id': this.id,
            'name': this.name,
            'mechanics': this.mechanics,
            'actions': actions,
        };
        
        this.encodeBlueprintAttributes(data);
        this.encodeSkillAttributes(data);
        this.encodeDocumentAttributes(data);

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
        if(this.mechanicsType !== "Blueprint") return;

        this.equipment = (new EquipmentConfig()).load(object.equipment);
        if (typeof object.ingredients !== 'undefined') {
            for (const [key, value] of Object.entries(object.ingredients)) {
                if (typeof key === 'string' && typeof value === 'number') {
                    this.ingredients?.set(key, value);
                }
            }
        }
    }

    private encodeBlueprintAttributes(data: any){
        if(this.mechanicsType !== "Blueprint") return;

        const ingredients : object = {};
        this.ingredients?.forEach((value, key) => {
            ingredients[key] = value;
        });

        data.equipment = this.equipment?.iri;
        data.ingredients = ingredients;
    }

    private encodeSkillAttributes(data: any){
        if(this.mechanicsType !== "Skill") return;

        data.skill = this.skill;
    }

    private addSkillAttributes(object: any){
        if(this.mechanicsType !== "Skill") return;

        this.skill = object.skill;
    }

    private addDocumentAttributes(object: any){
        if(this.mechanicsType !== "Document") return;

        this.content = object.content;
        this.isTranslated = object.isTranslated;
        this.canShred = object.canShred;
    }

    private encodeDocumentAttributes(data: any){
        if(this.mechanicsType !== "Document") return;

        data.content = this.content;
        data.isTranslated = this.isTranslated;
        data.canShred = this.canShred;
    }
}
