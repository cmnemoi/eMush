export type TradeOption = {
    id: number;
    name: string;
    description: string;
    tradeConditionsAreNotMet: string;
}

type TradeData = {
    id?: number;
    description?: string;
    options?: TradeOption[];
    image?: string;
};

export class Trade {
    public id!: number;
    public description!: string;
    public options!: TradeOption[];
    public image!: string;

    public load(object: TradeData): Trade {
        this.id = object.id;
        this.description = object.description;
        this.options = object.options;
        this.image = object.image;

        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: string): Trade {
        return JSON.parse(jsonString);
    }
}
