import ApiService from "@/services/api.service";
import { Channel } from "@/entities/Channel";
import { Message } from "@/entities/Message";
import { RoomLog } from "@/entities/RoomLog";
import { Player } from "@/entities/Player";
import { ChannelType } from "@/enums/communication.enum";
import { AxiosResponse } from "axios";
import urlJoin from "url-join";

const API_URL = import.meta.env.VITE_APP_API_URL as string;

const CAN_CREATE_CHANNEL_ENDPOINT = urlJoin(API_URL, "channel/canCreatePrivate");
const CHANNELS_ENDPOINT = urlJoin(API_URL, "channel");
const PIRATED_CHANNELS_ENDPOINT = urlJoin(API_URL, "channel/pirated");
const ROOM_LOGS_ENDPOINT = urlJoin(API_URL, "room-log");
const ROOM_LOGS_CHANNEL_ENDPOINT = urlJoin(API_URL, "room-log/channel");

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

        const favoritesChannelData = await ApiService.get(urlJoin(CHANNELS_ENDPOINT, 'favorites'));
        if (favoritesChannelData.data) {
            channels.push((new Channel()).load(favoritesChannelData.data));
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

    loadMessages: async (channel: Channel, page: integer = 1, limit: integer = Channel.MESSAGE_LIMIT): Promise<Array<Message|Record<string, unknown>>> => {
        if (channel.scope === ChannelType.ROOM_LOG) {
            return await loadRoomLogs();
        } else if (channel.scope === ChannelType.FAVORITES) {
            return await loadFavoritesChannelMessages();
        } else {
            return await CommunicationService.loadChannelMessages(channel, page, limit);
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

        async function loadFavoritesChannelMessages(): Promise<Message[]> {
            const messagesData = await ApiService.get(urlJoin(CHANNELS_ENDPOINT, 'favorites', 'messages'));

            const messages: Message[] = [];
            if (messagesData.data) {
                messagesData.data.forEach((data: any) => {
                    messages.push((new Message()).load(data));
                });
            }
            return messages;
        }
    },

    loadChannelMessages: async (channel: Channel, page: integer, limit: integer): Promise<Message[]> => {
        const messagesData = await ApiService.get(urlJoin(CHANNELS_ENDPOINT, String(channel.id), 'message'), {
            params: {
                'page': page,
                'limit': limit
            }
        });

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

    markChannelAsRead: async (channel: Channel): Promise<void> => {
        await ApiService.patch(urlJoin(CHANNELS_ENDPOINT, 'read', String(channel.id)));
    },

    markAllRoomLogsAsRead: async (): Promise<void> => {
        await ApiService.patch(urlJoin(ROOM_LOGS_ENDPOINT, 'read-all'));
    },

    putMessageInFavorite: async (message: Message): Promise<void> => {
        await ApiService.post(urlJoin(CHANNELS_ENDPOINT, 'favorite-message', String(message.id)));
    },


    readMessage: async (message: Message): Promise<void> => {
        await ApiService.patch(urlJoin(CHANNELS_ENDPOINT, 'read-message', String(message.id)));
        message.isUnread = false;
    },

    readRoomLog: async (roomLog: RoomLog): Promise<void> => {
        await ApiService.patch(urlJoin(ROOM_LOGS_ENDPOINT, 'read', String(roomLog.id)));
        roomLog.isUnread = false;
    },

    removeMessageFromFavorite: async (message: Message): Promise<void> => {
        await ApiService.delete(urlJoin(CHANNELS_ENDPOINT, 'unfavorite-message', String(message.id)));
    },

    sendMessage: async (channel: Channel, text: string, parent?: Message): Promise<Message[]> => {

        let parentId = null;
        if (typeof parent !== "undefined") {
            parentId = parent.id;
        }

        const messagesData = await ApiService.post(CHANNELS_ENDPOINT + '/' + channel.id + '/message', {
            'message': text,
            'parent': parentId,
            'player': channel.piratedPlayer,
            'page': 1,
            'limit': Channel.MESSAGE_LIMIT
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
