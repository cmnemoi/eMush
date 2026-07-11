import { PollOption } from "./PollOption";

export type PollData = {
    "@id"?: string;
    id?: number;
    title?: string;
    voteCount?: number;
    canVote?: boolean;
    remainingVotes?: number;
    voted?: boolean;
    isClosed?: boolean;
    options?: Array<PollOption>;
};

export class Poll {
    public iri: string|null;
    public id!: number;
    public title!: string;
    public voteCount!: number;
    public canVote!: boolean;
    public remainingVotes!: number;
    public voted!: boolean;
    public closed!: boolean;

    public options: Array<PollOption>;

    constructor() {
        this.options = new Array<PollOption>();
        this.iri = null;
    }

    public load(object: PollData): Poll {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.title = object.title;
            this.voteCount = object.voteCount;
            this.canVote = object.canVote;
            this.remainingVotes = object.remainingVotes;
            this.voted = object.voted;
            this.closed = object.isClosed;
            this.iri = object["@id"];

            this.options = [];
            object.options.forEach((optionObject: PollOption) => {
                this.options.push((new PollOption).load(optionObject));
            });
        }

        return this;
    }

    public reduceVote():void
    {
        this.remainingVotes -=1;
    }

    public increaseVote():void
    {
        this.remainingVotes +=1;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString : string): Poll {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
