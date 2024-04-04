export class DaedalusExploration {
    public title!: string;
    public planet!: string;
    public explorators!: string;
    public estimatedDuration!: string;

    public load(object: any): DaedalusExploration {
        if (object) {
            this.title = object.title;
            this.planet = object.planet;
            this.explorators = object.explorators;
            this.estimatedDuration = object.estimatedDuration;
        }
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString : string): DaedalusExploration {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
