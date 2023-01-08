export class ActionVariables {
    public actionPoint: number|null;
    public movementPoint: number|null;
    public moralPoint: number|null;
    public percentageInjury: number|null;
    public percentageDirtiness: number|null;
    public percentageSuccess: number|null;
    public percentageCritical: number|null;
    public isSuperDirty: boolean|null;

    constructor() {
        this.actionPoint = null;
        this.movementPoint = null;
        this.moralPoint = null;
        this.percentageInjury = null;
        this.percentageDirtiness = null;
        this.percentageSuccess = null;
        this.percentageCritical = null;
        this.isSuperDirty = null;
    }
    load(object:any) : ActionVariables {
        if (typeof object !== "undefined") {
            this.actionPoint = object.actionPoint;
            this.movementPoint = object['movementPoint'];
            this.moralPoint = object['moralPoint'];
            this.percentageInjury = object['percentageInjury'];
            this.percentageDirtiness = object['percentageDirtiness'];
            this.percentageSuccess = object['percentageSuccess'];
            this.percentageCritical = object['percentageCritical'];
            this.isSuperDirty = object['isSuperDirty'];
        }
        return this;
    }

    jsonEncode() : object {
        return {
            'actionPoint': this.actionPoint,
            'movementPoint': this.movementPoint,
            'moralPoint': this.moralPoint,
            'percentageInjury': this.percentageInjury,
            'percentageDirtiness': this.percentageDirtiness,
            'percentageSuccess': this.percentageSuccess,
            'percentageCritical': this.percentageCritical,
            'isSuperDirty': this.isSuperDirty
        };
    }
}
