export class PlayerVariables {
    public healthPoint!: integer;
    public moralPoint!: integer;
    public actionPoint!: integer;
    public movementPoint!: integer;
    public satiety!: integer;
    public spores!: integer;

    public load(object: any): PlayerVariables {
        if (object !== undefined && object !== null) {
            this.healthPoint = object.healthPoint;
            this.moralPoint = object.moralPoint;
            this.actionPoint = object.actionPoint;
            this.movementPoint = object.movementPoint;
            this.satiety = object.satiety;
            this.spores = object.spores;
        }
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: string): PlayerVariables {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}