export class HunterConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public hunterName: string|null;
    public initialHealth: number|null;
    public initialCharge: number|null;
    public initialArmor: number|null;
    public minDamage: number|null;
    public maxDamage: number|null;
    public hitChance: number|null;
    public dodgeChance: number|null;
    public drawCost: number|null;
    public maxPerWave: number|null;
    public drawWeight: number|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.hunterName = null;
        this.initialHealth = null;
        this.initialCharge = null;
        this.initialArmor = null;
        this.minDamage = null;
        this.maxDamage = null;
        this.hitChance = null;
        this.dodgeChance = null;
        this.drawCost = null;
        this.maxPerWave = null;
        this.drawWeight = null;
    }
    load(object:any) : HunterConfig {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object['id'];
            this.name = object['name'];
            this.hunterName = object['hunterName'];
            this.initialHealth = object['initialHealth'];
            this.initialCharge = object['initialCharge'];
            this.initialArmor = object['initialArmor'];
            this.minDamage = object['minDamage'];
            this.maxDamage = object['maxDamage'];
            this.hitChance = object['hitChance'];
            this.dodgeChance = object['dodgeChance'];
            this.drawCost = object['drawCost'];
            this.maxPerWave = object['maxPerWave'];
            this.drawWeight = object['drawWeight'];
        }
        return this;
    }
    jsonEncode() : any {
        return {
            "id": this.id,
            "name": this.name,
            "hunterName": this.hunterName,
            "initialHealth": this.initialHealth,
            "initialCharge": this.initialCharge,
            "initialArmor": this.initialArmor,
            "minDamage": this.minDamage,
            "maxDamage": this.maxDamage,
            "hitChance": this.hitChance,
            "dodgeChance": this.dodgeChance,
            "drawCost": this.drawCost,
            "maxPerWave": this.maxPerWave,
            "drawWeight": this.drawWeight,
        };
    }
    decode(jsonString : string): HunterConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }
        return this;
    }
}
