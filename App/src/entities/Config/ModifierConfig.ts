export class ModifierConfig {
    public id: number|null;
    public name: string|null;
    public delta: number|null;
    public target: string|null;
    public scope: string|null;
    public reach: string|null;
    public mode: string|null;


    constructor() {
        this.id = null;
        this.name = null;
        this.delta = null;
        this.target = null;
        this.scope = null;
        this.reach = null;
        this.mode = null;
    }
    load(object:any) : ModifierConfig {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.name = object.name;
            this.delta = object.delta;
            this.target = object.target;
            this.scope = object.scope;
            this.reach = object.reach;
            this.mode = object.mode;
        }
        return this;
    }
    jsonEncode() : string {
        return JSON.stringify(this);
    }
    decode(jsonString : string): ModifierConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.id = object.id;
            this.name = object.name;
            this.delta = object.delta;
            this.target = object.target;
            this.scope = object.scope;
            this.reach = object.reach;
            this.mode = object.mode;
        }

        return this;
    }
}
