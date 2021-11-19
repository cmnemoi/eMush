import ApiService from "@/services/api.service";
import { Channel } from "@/entities/Channel";
import { Message } from "@/entities/Message";
import { RoomLog } from "@/entities/RoomLog";
import { Player } from "@/entities/Player";
import { ChannelType } from "@/enums/communication.enum";
import { AxiosResponse } from "axios";
import urlJoin from "url-join";

const CHANNELS_ENDPOINT = urlJoin((process.env.VUE_APP_API_URL) as string, "channel");
const ROOM_LOGS_ENDPOINT = urlJoin((process.env.VUE_APP_API_URL) as string, "room-log");

const CommunicationService = {

    loadChannels: async(): Promise<Channel[]> => {
        const channelsData = await ApiService.get(CHANNELS_ENDPOINT);

        const channels = [
            (new Channel()).load({ scope: ChannelType.TIPS, id: ChannelType.TIPS }),
            (new Channel()).load({ scope: ChannelType.ROOM_LOG, id: ChannelType.ROOM_LOG })
        ];
        if (channelsData.data) {
            channelsData.data.forEach((data: Channel) => {
                channels.push((new Channel()).load(data));
            });
        }

        return channels;
    },

    createPrivateChannel: async (): Promise<Channel> => {
        const response = await ApiService.post(CHANNELS_ENDPOINT);
        return (new Channel()).load(response.data);
    },

    leaveChannel: async (channel: Channel): Promise<AxiosResponse> => {
        return ApiService.post(CHANNELS_ENDPOINT + '/' + channel.id + '/exit');
    },

    loadMessages: async (channel: Channel): Promise<Array<Message|Record<string, unknown>>> => {
        switch (channel.scope) {
        case ChannelType.PRIVATE:
        case ChannelType.PUBLIC:
            return loadChannelMessages();
        case ChannelType.ROOM_LOG:
            return loadRoomLogs();
        default:
            return [];
        }

        async function loadChannelMessages(): Promise<Message[]> {
            const messagesData = await ApiService.get(CHANNELS_ENDPOINT + '/' + channel.id + '/message');

            const messages: Message[] = [];
            if (messagesData.data) {
                messagesData.data.forEach((data: Message) => {
                    messages.push((new Message()).load(data));
                });
            }
            return messages;
        }

        async function loadRoomLogs(): Promise<Record<string, unknown>[]> {
            const result = await ApiService.get(ROOM_LOGS_ENDPOINT);

            const logs: Record<string, unknown>[] = [];
            if (result.data) {
                const days = result.data;
                Object.keys(days).map((day) => {
                    Object.keys(days[day]).map((cycle) => {
                        const roomLogs: RoomLog[] = [];
                        days[day][cycle].forEach((value: string) => {
                            const roomLog = (new RoomLog()).load(value);
                            roomLogs.push(roomLog);
                        });
                        logs.push({
                            "day": day,
                            "cycle": cycle,
                            roomLogs
                        });
                    });
                });
            }
            return logs;
        }
    },

    loadInvitablePlayers: async (channel: Channel): Promise<Player[]> => {
        const playersData = await ApiService.get(CHANNELS_ENDPOINT + '/' + channel.id + '/invite');

        const players:Array<Player> = [];
        if (playersData.data) {
            playersData.data.forEach((data: Player) => {
                players.push((new Player()).load(data));
            });
        }
        return players;
    },

    invitePlayer: async (player:Player, channel: Channel): Promise<void> => {
        await ApiService.post(CHANNELS_ENDPOINT + '/' + channel.id + '/invite', {
            player: player.id
        });
    },

    sendMessage: async (channel: Channel, text: string, parent?: Message): Promise<Message[]> => {

        let parentId = null;
        if (typeof parent !== "undefined") {
            parentId = parent.id;
        }

        const messagesData = await ApiService.post(CHANNELS_ENDPOINT + '/' + channel.id + '/message', {
            'message': text,
            'parent': parentId
        });

        const messages: Message[] = [];
        if (messagesData.data) {
            messagesData.data.forEach((data: Message) => {
                messages.push((new Message()).load(data));
            });
        }
        return messages;
    }
};
export default CommunicationService;
