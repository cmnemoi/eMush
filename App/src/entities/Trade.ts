export type TradeOption = {
    id: number;
    name: string;
    description: string;
    tradeConditionsAreNotMet: string;
}

export class Trade {
    public id!: number;
    public description!: string;
    public options!: TradeOption[];
    public image!: string;

    public load(object: any): Trade {
        this.id = object.id;
        this.description = object.description;
        this.options = object.options;
        this.image = object.image;

        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: any): Trade {
        return JSON.parse(jsonString);
    }
}
