import { ChannelParticipant } from "./ChannelParticipant";
import { ChannelType } from "@/enums/communication.enum";

export class Channel {
    public id!: number;
    public scope!: ChannelType;
    public participants: Array<ChannelParticipant>;
    public newMessageAllowed = false;
    public piratedPlayer: number | null = null;

    constructor() {
        this.participants = [];
    }

    load(object : any) : Channel {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.scope = object.scope;
            this.newMessageAllowed = object.newMessageAllowed;
            this.piratedPlayer = object.piratedPlayer;
            if (typeof object.participants !== 'undefined') {
                object.participants.forEach((itemObject: any) => {
                    const participant = (new ChannelParticipant()).load(itemObject);
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
            const object = JSON.parse(jsonString);
            this.id = object.id;
            this.scope = object.scope;
            this.participants = object.participants;
        }

        return this;
    }

    getParticipant(key: string): ChannelParticipant | undefined {
        return this.participants.find((element: ChannelParticipant) => element.character !== null && element.character.key === key);
    }
}
