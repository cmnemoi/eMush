import { Character, CharacterData } from "./Character";

export type ChannelParticipantData = {
    id?: number;
    character?: CharacterData;
    joinedAt?: string;
};

export class ChannelParticipant {
    public id: number|null;
    public character: Character|null;
    public joinedAt: Date;

    constructor() {
        this.id = null;
        this.character = null;
        this.joinedAt = new Date();
    }

    load(object : ChannelParticipantData): ChannelParticipant {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.character = (new Character()).load(object.character);
            this.joinedAt = new Date(object.joinedAt);
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: string): ChannelParticipant {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }
        return this;
    }
}
