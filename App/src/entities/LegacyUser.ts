export class LegacyUser {
    public id!: number;
    public twinoidId!: number;
    public twinoidUsername!: string;
    public characterLevels!: Map<string, number>;
    public stats!: any;
    public achievements!: any;
    public historyHeroes: any;
    public historyShips: any;

    public load(object: any): LegacyUser {
        if (object) {
            this.id = object.id;
            this.twinoidId = object.twinoidProfile.twinoidId;
            this.twinoidUsername = object.twinoidProfile.twinoidUsername;
            this.characterLevels = object.characterLevels;
            this.historyHeroes = object.historyHeroes;
            this.historyShips = object.historyShips;
            this.stats = object.twinoidProfile.stats;
            this.achievements = object.twinoidProfile.achievements;
        }

        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this, null, 2);
    }

    public decode(jsonString: any): LegacyUser {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}