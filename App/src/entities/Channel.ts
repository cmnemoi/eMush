import { ChannelParticipant } from "./ChannelParticipant";

export class Channel {
    public id: number|null;
    public scope: string;
    public participants: Array<ChannelParticipant>;

    constructor() {
        this.id = null;
        this.scope = 'default';
        this.participants = [];
    }

    load(object : any) : Channel {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.scope = object.scope;
            if (typeof object.participants !== 'undefined') {
                object.participants.forEach((itemObject: any) => {
                    let participant = (new ChannelParticipant()).load(itemObject);
                    this.participants.push(participant);
                });
            }
        }
        return this;
    }
    jsonEncode() : string {
        return JSON.stringify(this);
    }
    decode(jsonString : string) : Channel {
        if (jsonString) {
            let object = JSON.parse(jsonString);
            this.id = object.id;
            this.scope = object.scope;
            this.participants = object.participants;
        }

        return this;
    }

    getParticipant(key: string) {
        return this.participants.find((element: ChannelParticipant) => element.character !== null && element.character.key === key);
    }
}
