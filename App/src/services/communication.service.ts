import ApiService from "@/services/api.service";
import { Channel } from "@/entities/Channel";
import { Message } from "@/entities/Message";
import { RoomLog } from "@/entities/RoomLog";
import { Player } from "@/entities/Player";
import { ChannelType } from "@/enums/communication.enum";
import { AxiosResponse } from "axios";
import urlJoin from "url-join";

// @ts-ignore
const CAN_CREATE_CHANNEL_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "channel/canCreatePrivate");
// @ts-ignore
const CHANNELS_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "channel");
// @ts-ignore
const PIRATED_CHANNELS_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "channel/pirated");
// @ts-ignore
const ROOM_LOGS_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "room-log");
// @ts-ignore
const ROOM_LOGS_CHANNEL_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "room-log/channel");

const CommunicationService = {
    loadChannels: async(): Promise<Channel[]> => {
        const channels = [];

        const roomLogChannelData = await ApiService.get(ROOM_LOGS_CHANNEL_ENDPOINT);
        if (roomLogChannelData.data) {
            channels.push((new Channel()).load(roomLogChannelData.data));
        }

        const channelsData = await ApiService.get(CHANNELS_ENDPOINT);
        if (channelsData.data) {
            // If there is only one channel available, the API returns an object instead of an array.
            // We need to handle this case to avoid not being able to load the channels.
            const dataArray = Array.isArray(channelsData.data) ? channelsData.data : Object.values(channelsData.data);
            dataArray.forEach((data: any) => {
                channels.push((new Channel()).load(data));
            });
        }

        const piratedChannelsData = await ApiService.get(PIRATED_CHANNELS_ENDPOINT);
        if (piratedChannelsData.data) {
            Object.values(piratedChannelsData.data).forEach((data: any) => {
                channels.push((new Channel()).load(data));
            });
        }

        const newChannelData = await ApiService.get(CAN_CREATE_CHANNEL_ENDPOINT);
        if (newChannelData.data && newChannelData.data['canCreate']) {
            channels.push((new Channel()).load({
                scope: ChannelType.NEW_CHANNEL,
                id: ChannelType.NEW_CHANNEL,
                name: newChannelData.data['name'],
                description: newChannelData.data['description']
            }));
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
        case ChannelType.MUSH:
            return CommunicationService.loadChannelMessages(channel);
        case ChannelType.ROOM_LOG:
            return loadRoomLogs();
        default:
            return [];
        }

        async function loadRoomLogs(): Promise<Record<string, unknown>[]> {
            const result = await ApiService.get(ROOM_LOGS_ENDPOINT);

            const logs: Record<string, unknown>[] = [];
            if (result.data) {
                const days = result.data;
                Object.keys(days).map((day) => {
                    Object.keys(days[day]).map((cycle) => {
                        const roomLogs: RoomLog[] = [];
                        days[day][cycle].forEach((value: any) => {
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

    loadChannelMessages: async (channel: Channel): Promise<Message[]> => {
        const messagesData = await ApiService.get(CHANNELS_ENDPOINT + '/' + channel.id + '/message');

        const messages: Message[] = [];
        if (messagesData.data) {
            messagesData.data.forEach((data: any) => {
                messages.push((new Message()).load(data));
            });
        }
        return messages;
    },

    loadInvitablePlayers: async (channel: Channel): Promise<Player[]> => {
        const playersData = await ApiService.get(CHANNELS_ENDPOINT + '/' + channel.id + '/invite');

        const players:Player[] = [];
        if (playersData.data) {
            playersData.data.forEach((data: any) => {
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
            'parent': parentId,
            'player': channel.piratedPlayer
        });

        const messages: Message[] = [];
        if (messagesData.data) {
            messagesData.data.forEach((data: any) => {
                messages.push((new Message()).load(data));
            });
        }
        return messages;
    }
};
export default CommunicationService;
