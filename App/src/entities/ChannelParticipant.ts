import { Character } from "./Character";

export interface ChannelParticipant {
    id: number,
    character: Character,
    joinedAt: Date
}
