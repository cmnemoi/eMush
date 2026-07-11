import { Equipment, EquipmentData } from "@/entities/Equipment";

type ItemData = EquipmentData & {
    number?: number;
    effects?: { title?: string; effects?: string[] };
};

export class Item extends Equipment {
    public number: number;
    public effectTitle: string;
    public effects: string[];

    constructor() {
        super();
        this.number = 0;
        this.effectTitle = '';
        this.effects = [];
    }
    load(object: ItemData): Item {
        super.load(object);
        if (typeof object !== "undefined") {
            this.number = object.number;
            if (typeof object.effects !== "undefined" && typeof object.effects.title !== "undefined") {
                this.effectTitle = object.effects.title;
                object.effects.effects.forEach((effect: string) => {
                    this.effects.push(effect);
                });
            }
        }
        return this;
    }
}
