export class TerminalInfos {
    public difficulty: string|null;

    constructor() {
        this.difficulty = null;
    }

    public load(object: any): TerminalInfos {
        if (object) {
            this.difficulty = object.difficulty;
        }
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: any): TerminalInfos {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }

}