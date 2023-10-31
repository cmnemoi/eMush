export class LegacyUser {
    public id!: number;
    public userId!: number;
    public createdAt!: string;
    public updatedAt!: string;
    public twinoidId!: number;
    public twinoidUsername!: string;
    public availableExperience!: number;
    public characterLevels!: Map<string, number>;
    public skins!: Array<string>;
    public flairs!: Array<string>;
    public klix!: number;
    public experienceResetKlixCost!: number;
    public stats!: any;
    public achievements!: any;
    public historyHeroes: any;
    public historyShips: any;
    public hidden = true;

    public load(object: any): LegacyUser {
        if (object) {
            this.id = object.id;
            this.createdAt = object.createdAt;
            this.updatedAt = object.updatedAt;
            this.userId = object.userId;
            this.twinoidId = object.twinoidId;
            this.twinoidUsername = object.twinoidUsername;
            this.historyHeroes = object.historyHeroes;
            this.historyShips = object.historyShips;
            this.stats = object.stats;
            this.achievements = object.achievements;
            this.availableExperience = object.availableExperience;
            this.characterLevels = object.characterLevels;
            this.skins = object.skins;
            this.flairs = object.flairs;
            this.klix = object.klix;
            this.experienceResetKlixCost = object.experienceResetKlixCost;
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

    public toggle(): void {
        this.hidden = !this.hidden;
    }
}
