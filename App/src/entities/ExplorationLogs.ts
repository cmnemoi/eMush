export class ExplorationLogs {
    public id!: number;
    public planetSectorKey!: string;
    public planetSectorName!: string;
    public eventName!: string;
    public eventDescription!: string;
    public eventOutcome!: string;

    public load(object: any): ExplorationLogs {
        if (object) {
            this.id = object.id;
            this.planetSectorKey = object.planetSectorKey;
            this.planetSectorName = object.planetSectorName;
            this.eventName = object.eventName;
            this.eventDescription = object.eventDescription;
            this.eventOutcome = object.eventOutcome;
        }

        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString : string): ExplorationLogs {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
