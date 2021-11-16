import { ChannelParticipant } from "./ChannelParticipant";
import { ChannelType } from "@/enums/communication.enum";

export interface Channel {
    id: number;
    scope: ChannelType;
    participants: Array<ChannelParticipant>;
}
