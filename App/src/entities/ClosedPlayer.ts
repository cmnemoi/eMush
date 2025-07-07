import { toArray } from "@/utils/toArray";

export class ClosedPlayer {
    public iri: string|null;
    public id: number|null;
    public message: string|null;
    public endCause: string|null;
    public dayDeath: integer|null;
    public cycleDeath: integer|null;
    public likes: integer;
    public isMush: boolean|null;
    public characterKey: string|null;
    public userId: string|null;
    public username: string|null;
    public closedDaedalusId: integer|null;
    public daysSurvived: integer|null;
    public cyclesSurvived: integer|null;
    public triumph: integer|null;
    public score: integer|null;
    public rank: integer|null;
    public language: string|null;
    public messageIsHidden: boolean|null;
    public messageIsEdited: boolean|null;
    public messageHasBeenModerated: boolean = false;
    public hasBadEndCause!: boolean;
    public triumphGains: string[] = [];
    public highlights: string[] = [];

    constructor() {
        this.iri = null;
        this.id = null;
        this.message = null;
        this.endCause = null;
        this.dayDeath = null;
        this.cycleDeath = null;
        this.likes = 0;
        this.isMush = null;
        this.characterKey = null;
        this.userId = null;
        this.username = null;
        this.closedDaedalusId = null;
        this.daysSurvived = null;
        this.cyclesSurvived = null;
        this.triumph = null;
        this.score = null;
        this.rank = null;
        this.language = null;
        this.messageIsHidden = null;
        this.messageIsEdited = null;
    }
    load(object :any): ClosedPlayer {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.message = object.message;
            this.endCause = object.endCause;
            this.dayDeath = object.dayDeath;
            this.cycleDeath = object.cycleDeath;
            this.likes = object.likes ?? 0;
            this.isMush = object.isMush;
            this.characterKey = object.characterKey;
            this.userId = object.userId;
            this.username = object.username;
            this.closedDaedalusId = object.closedDaedalusId;
            this.daysSurvived = object.daysSurvived;
            this.cyclesSurvived = object.cyclesSurvived;
            this.triumph = object.triumph;
            this.rank = object.rank;
            this.language = object.language;
            this.messageIsHidden = object.messageIsHidden;
            this.messageIsEdited = object.messageIsEdited;
            this.messageHasBeenModerated = (this.messageIsEdited || this.messageIsHidden) ?? false;
            this.hasBadEndCause = ['sol_return', 'eden'].includes(this.endCause ?? '') ? false : true;
            this.score = this.triumph ?? this.cyclesSurvived;
            if (object.triumphGains) {
                toArray(object.triumphGains).forEach((triumphGainObject: string) => {
                    this.triumphGains.push(triumphGainObject);
                });
            }
            if (object.playerHighlights) {
                toArray(object.playerHighlights).forEach((highlightObject: string) => {
                    this.highlights.push(highlightObject);
                });
            }
        }
        return this;
    }
    jsonEncode(): object {
        const data : any = {
            'id': this.id,
            'message': this.message,
            'endCause': this.endCause,
            'dayDeath': this.dayDeath,
            'cycleDeath': this.cycleDeath,
            'likes': this.likes,
            'isMush': this.isMush,
            'characterKey': this.characterKey,
            'userId': this.userId,
            'username': this.username,
            'closedDaedalusId': this.closedDaedalusId,
            'daysSurvived': this.daysSurvived,
            'cyclesSurvived': this.cyclesSurvived,
            'triumph': this.triumph,
            'rank': this.rank,
            'language': this.language,
            'messageIsHidden': this.messageIsHidden,
            'messageIsEdited': this.messageIsEdited,
            'messageHasBeenModerated': this.messageHasBeenModerated,
            'hasBadEndCause': this.hasBadEndCause
        };

        return data;
    }
    decode(jsonString : string): ClosedPlayer {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
