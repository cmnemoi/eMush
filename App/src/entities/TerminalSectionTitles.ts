export class TerminalSectionTitles {
    public orientateDaedalus: string|null = null;
    public moveDaedalus: string|null = null;
    public generalInformations: string|null = null;

    public load(object: any): TerminalSectionTitles {
        if (object) {
            this.orientateDaedalus = object['orientate_daedalus'];
            this.moveDaedalus = object['move_daedalus'];
            this.generalInformations = object['general_informations'];
        }
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: any): TerminalSectionTitles {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}