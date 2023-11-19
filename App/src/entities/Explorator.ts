import { characterEnum } from "@/enums/character";

export class Explorator {
    public key!: string;
    public name!: string;
    public healthPoints!: number;
    public isAlive!: boolean;

    public load(object: any): Explorator {
        if (object) {
            this.key = object.key;
            this.name = object.name;
            this.healthPoints = object.healthPoints;
            this.isAlive = object.isAlive;
        }

        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: string): Explorator {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }

    public getExploratorBody() {
        return characterEnum[this.key].body;
    }
}