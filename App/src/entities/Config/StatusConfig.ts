import { ModifierConfig } from "@/entities/Config/ModifierConfig";

export class StatusConfig {
    public iri: string|null;
    public configType: string|null;
    public id: number|null;
    public name: string|null;
    public statusName: string|null;
    public visibility: string|null;
    public chargeVisibility: string|null;
    public chargeStrategy: string|null;
    public maxCharge: number|null;
    public startCharge: number|null;
    public dischargeStrategy: string|null;
    public autoRemove: boolean|null;
    public modifierConfigs: ModifierConfig[]|null;

    constructor() {
        this.iri = null;
        this.configType = null;
        this.id = null;
        this.name = null;
        this.statusName = null;
        this.visibility = null;
        this.chargeVisibility = null;
        this.chargeStrategy = null;
        this.maxCharge = null;
        this.startCharge = null;
        this.dischargeStrategy = null;
        this.autoRemove = null;
        this.modifierConfigs = null;
    }
    load(object:any) : StatusConfig {
        if (typeof object !== "undefined") {
            this.iri = object["@id"];
            this.id = object.id;
            this.name = object.name;
            this.statusName = object.statusName;
            this.visibility = object.visibility;
            if (typeof object.modifierConfigs !== 'undefined') {
                const modifierConfigs : ModifierConfig[] = [];
                object.modifierConfigs.forEach((modifierConfigData: any) => {
                    const modifierConfig = (new ModifierConfig()).load(modifierConfigData);
                    modifierConfigs.push(modifierConfig);
                });
                this.modifierConfigs = modifierConfigs;
            }
            this.configType = object['@type'];
            if (this.configType === 'ChargeStatusConfig') {
                this.chargeVisibility = object.chargeVisibility;
                this.chargeStrategy = object.chargeStrategy;
                this.dischargeStrategy = object.dischargeStrategy;
                this.maxCharge = object.maxCharge;
                this.startCharge = object.startCharge;
                this.autoRemove = object.autoRemove;
            }
        }
        return this;
    }
    jsonEncode() : any {
        const modifierConfigs : string[] = [];
        this.modifierConfigs?.forEach(modifierConfig => (typeof modifierConfig.iri === 'string' ? modifierConfigs.push(modifierConfig.iri) : null));
        const data : any = {
            'id': this.id,
            'name': this.name,
            'statusName': this.statusName,
            'visibility': this.visibility,
            'modifierConfigs': modifierConfigs
        };
        if (this.configType === 'ChargeStatusConfig') {
            data['chargeVisibility'] = this.chargeVisibility;
            data['chargeStrategy'] = this.chargeStrategy;
            data['dischargeStrategy'] = this.dischargeStrategy;
            data['maxCharge'] = this.maxCharge;
            data['startCharge'] = this.startCharge;
            data['autoRemove'] = this.autoRemove;
        }
        return data;
    }

    decode(jsonString: string): StatusConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
