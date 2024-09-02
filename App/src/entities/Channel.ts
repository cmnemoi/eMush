import { ChannelParticipant } from "./ChannelParticipant";
import { ChannelType } from "@/enums/communication.enum";

export type CommanderMission = {
    id: number,
    commander: {id: number, key: string, name: string},
    mission: string,
    date: string,
    isPending: boolean,
    isCompleted: boolean,
};

type Tips = {
    teamObjectives: {
        title: string;
        elements: string[];
        tutorial?: {
            title: string;
            link: string;
        }
    };
    characterObjectives: {
        title: string;
        elements: string[];
        tutorial: {
            title: string;
            link: string;
        }
    };
    externalResources: {
        title: string;
        elements: Array<{ text: string, link?: string }>;
    };
    missions: {
        title: string;
        elements: CommanderMission[];
        buttons: {
            accept: string;
        }
    };
}

export class Channel {

    static get MESSAGE_LIMIT() {
        return 20;
    }

    public id!: number;
    public createdAt!: Date;
    public scope!: ChannelType;
    public participants: Array<ChannelParticipant>;
    public newMessageAllowed = false;
    public piratedPlayer: number | null = null;
    public name!: string;
    public description!: string;
    public numberOfNewMessages!: integer;
    public tips?: Tips;

    constructor() {
        this.participants = [];
    }

    load(object : any) : Channel {
        if (object) {
            this.id = object.id;
            this.createdAt = new Date(object.createdAt);
            this.scope = object.scope;
            this.newMessageAllowed = object.newMessageAllowed;
            this.piratedPlayer = object.piratedPlayer;
            if (object.participants) {
                object.participants.forEach((itemObject: any) => {
                    const participant = (new ChannelParticipant()).load(itemObject);
                    this.participants.push(participant);
                });
            }

            this.name = object.name;
            this.description = object.description;
            this.numberOfNewMessages = object.numberOfNewMessages;
            this.tips = object.tips;
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
            this.name = object.name;
            this.description = object.description;
            this.numberOfNewMessages = object.numberOfNewMessages;
            this.tips = object.tips;
        }

        return this;
    }

    getParticipant(key: string): ChannelParticipant | undefined {
        return this.participants.find((element: ChannelParticipant) => element.character !== null && element.character.key === key);
    }

    isChannelWithPagination(): boolean {
        return [ChannelType.PUBLIC, ChannelType.PRIVATE].includes(this.scope);
    }

    isFavorite(): boolean {
        return this.scope === ChannelType.FAVORITES;
    }

    isNotTipsChannel(): boolean {
        return this.scope !== ChannelType.TIPS;
    }

    supportsReplies(): boolean {
        return [ChannelType.PUBLIC, ChannelType.FAVORITES].includes(this.scope);
    }

    supportsFavorite(): boolean {
        return [ChannelType.PUBLIC, ChannelType.FAVORITES].includes(this.scope);
    }
}
