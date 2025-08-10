export type ChannelScope = "public" | "mush" | "private";

export type ModerationChannelParticipant = {
    id: number;
    character: {
        key: string;
        value: string;
    };
    joinedAt: string;
};

export type ModerationChannel = {
    id: number;
    scope: ChannelScope;
    name: string;
    participants: ModerationChannelParticipant[];
    allTimeParticipants: ModerationChannelParticipant[];
};
