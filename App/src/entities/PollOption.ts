export class PollOption {
    public id!: number;
    public name!: string;
    public votes!: number;
    public voted!: boolean;

    public load(object: any): PollOption {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.name = object.name;
            this.votes = object.votes;
            this.voted = object.voted;
        }
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString : string): PollOption {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
