export class TerminalButtons {
    public sharePlanet!: TerminalButton;

    public load(object: any): TerminalButtons {
        if (object) {
            this.sharePlanet = object.share_planet;
        }
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: any): TerminalButtons {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}

type TerminalButton = {
    name: string;
    description: string;
}
