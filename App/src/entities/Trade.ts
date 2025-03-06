type TradeOption = {
    name: string;
    description: string;
}

export class Trade {
    public key!: string;
    public description!: string;
    public options!: TradeOption[];

    public load(object: any): Trade {
        this.key = object.key;
        this.description = object.description;
        this.options = object.options;
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: any): Trade {
        return JSON.parse(jsonString);
    }
}
