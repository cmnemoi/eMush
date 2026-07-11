type TerminalButtonsData = {
    share_planet?: TerminalButton;
    share_projects?: TerminalButton;
};

export class TerminalButtons {
    public sharePlanet!: TerminalButton;
    public shareProjects!: TerminalButton;

    public load(object: TerminalButtonsData): TerminalButtons {
        if (object) {
            this.sharePlanet = object.share_planet;
            this.shareProjects = object.share_projects;
        }
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: string): TerminalButtons {
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
