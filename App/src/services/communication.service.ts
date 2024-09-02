import ApiService from "@/services/api.service";
import { Channel } from "@/entities/Channel";
import { Message } from "@/entities/Message";
import { RoomLog } from "@/entities/RoomLog";
import { Player } from "@/entities/Player";
import { ChannelType } from "@/enums/communication.enum";
import { AxiosResponse } from "axios";
import urlJoin from "url-join";
import { ContactablePlayer } from "@/entities/ContactablePlayer";

const API_URL = import.meta.env.VITE_APP_API_URL as string;

const CAN_CREATE_CHANNEL_ENDPOINT = urlJoin(API_URL, "channel/canCreatePrivate");
const CHANNELS_ENDPOINT = urlJoin(API_URL, "channel");
const PIRATED_CHANNELS_ENDPOINT = urlJoin(API_URL, "channel/pirated");
const ROOM_LOGS_ENDPOINT = urlJoin(API_URL, "room-log");
const ROOM_LOGS_CHANNEL_ENDPOINT = urlJoin(API_URL, "room-log/channel");
const TIPS_CHANNEL_ENDPOINT = urlJoin(API_URL, "channel/tips");
const FAVORITES_CHANNEL_ENDPOINT = urlJoin(API_URL, "channel/favorites");
const PLAYER_ENDPOINT = urlJoin(API_URL, "player");

// If there is only one element found, the API returns an object instead of an array.
// We need to handle this case to avoid not being able to load the channels / messages.
function toArray(data: any): any[] {
    return Array.isArray(data) ? data : Object.values(data);
}

const CommunicationService = {
    loadAlivePlayerChannels: async(): Promise<Channel[]> => {
        const channelsPromise = CommunicationService.loadDeadPlayerChannels();
        const favoritesChannelPromise = ApiService.get(FAVORITES_CHANNEL_ENDPOINT);
        const piratedChannelsPromise = ApiService.get(PIRATED_CHANNELS_ENDPOINT);
        const newChannelPromise = ApiService.get(CAN_CREATE_CHANNEL_ENDPOINT);
        const tipsChannelPromise = ApiService.get(TIPS_CHANNEL_ENDPOINT);

        const channels = await channelsPromise.then((channels: Channel[]) => {
            return channels;
        });
        await favoritesChannelPromise.then((favoritesChannelData: any) => {
            if (favoritesChannelData.data) {
                channels.push((new Channel()).load(favoritesChannelData.data));
            }
        });
        await piratedChannelsPromise.then((piratedChannelsData: any) => {
            if (piratedChannelsData.data) {
                toArray(piratedChannelsData.data).forEach((data: any) => {
                    channels.push((new Channel()).load(data));
                });
            }
        });
        await newChannelPromise.then((newChannelData: any) => {
            if (newChannelData.data && newChannelData.data['canCreate']) {
                channels.push((new Channel()).load({
                    scope: ChannelType.NEW_CHANNEL,
                    id: ChannelType.NEW_CHANNEL,
                    name: newChannelData.data['name'],
                    description: newChannelData.data['description']
                }));
            }
        });
        await tipsChannelPromise.then((tipsChannelData: any) => {
            if (tipsChannelData.data) {
                channels.push((new Channel()).load({
                    scope: ChannelType.TIPS,
                    id: ChannelType.TIPS,
                    name: tipsChannelData.data.name,
                    description: tipsChannelData.data.description,
                    tips: tipsChannelData.data.tips
                }));
            }
        });

        return channels;
    },

    loadDeadPlayerChannels: async (): Promise<Channel[]> => {
        const channels: Channel[] = [];

        const roomLogChannelPromise = ApiService.get(ROOM_LOGS_CHANNEL_ENDPOINT);
        const channelsPromise = ApiService.get(CHANNELS_ENDPOINT);

        await roomLogChannelPromise.then((roomLogChannelData: any) => {
            if (roomLogChannelData.data) {
                channels.push((new Channel()).load(roomLogChannelData.data));
            }
        });
        await channelsPromise.then((channelsData: any) => {
            if (channelsData.data) {
                toArray(channelsData.data).forEach((data: any) => {
                    channels.push((new Channel()).load(data));
                });
            }
        });

        return channels;
    },

    createPrivateChannel: async (): Promise<Channel> => {
        const response = await ApiService.post(CHANNELS_ENDPOINT);
        return (new Channel()).load(response.data);
    },

    getContactablePlayers: async(player: Player): Promise<ContactablePlayer[]> => {
        const playersData = await ApiService.get(urlJoin(PLAYER_ENDPOINT, String(player.id), 'contactable-players'));

        const players: ContactablePlayer[] = [];
        if (playersData.data) {
            toArray(playersData.data).forEach((data: any) => {
                players.push((new ContactablePlayer()).load(data));
            });
        }
        return players;
    },

    leaveChannel: async (channel: Channel): Promise<AxiosResponse> => {
        return ApiService.post(CHANNELS_ENDPOINT + '/' + channel.id + '/exit');
    },

    loadMessages: async (channel: Channel, timeLimit: integer = 48): Promise<Array<Message|Record<string, unknown>>> => {
        if (channel.scope === ChannelType.TIPS) {
            return [];
        } else if (channel.scope === ChannelType.ROOM_LOG) {
            return await loadRoomLogs();
        } else if (channel.scope === ChannelType.FAVORITES) {
            return await loadFavoritesChannelMessages(timeLimit);
        } else {
            return await CommunicationService.loadChannelMessages(channel, timeLimit);
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

        async function loadFavoritesChannelMessages(timeLimit: integer): Promise<Message[]> {
            const messagesData = await ApiService.get(urlJoin(CHANNELS_ENDPOINT, 'favorites', 'messages'), {
                params: {
                    timeLimit: timeLimit
                }
            });

            const messages: Message[] = [];
            if (messagesData.data) {
                toArray(messagesData.data).forEach((data: any) => {
                    messages.push((new Message()).load(data));
                });
            }
            return messages;
        }
    },

    loadChannelMessages: async (channel: Channel, timeLimit: integer): Promise<Message[]> => {
        const messagesData = await ApiService.get(urlJoin(CHANNELS_ENDPOINT, String(channel.id), 'message'), {
            params: {
                'timeLimit': timeLimit
            }
        });

        const messages: Message[] = [];
        if (messagesData.data) {
            toArray(messagesData.data).forEach((data: any) => {
                messages.push((new Message()).load(data));
            });
        }
        return messages;
    },

    loadInvitablePlayers: async (channel: Channel): Promise<ContactablePlayer[]> => {
        const playersData = await ApiService.get(CHANNELS_ENDPOINT + '/' + channel.id + '/invite');

        const players : ContactablePlayer[] = [];
        if (playersData.data) {
            toArray(playersData.data).forEach((data: any) => {
                players.push((new ContactablePlayer()).load(data));
            });
        }
        return players;
    },

    invitePlayer: async (player:Player, channel: Channel): Promise<void> => {
        await ApiService.post(CHANNELS_ENDPOINT + '/' + channel.id + '/invite', {
            player: player.id
        });
    },

    markAllRoomLogsAsRead: async (): Promise<void> => {
        await ApiService.patch(urlJoin(ROOM_LOGS_ENDPOINT, 'all', 'read'));
    },

    markChannelAsRead: async (channel: Channel): Promise<void> => {
        await ApiService.patch(urlJoin(CHANNELS_ENDPOINT, String(channel.id), 'read'));
    },

    putMessageInFavorite: async (message: Message): Promise<void> => {
        await ApiService.post(urlJoin(CHANNELS_ENDPOINT, 'favorite-message', String(message.id)));
    },

    readMessage: async (message: Message): Promise<void> => {
        message.isUnread = false;
        await ApiService.patch(urlJoin(CHANNELS_ENDPOINT, 'read-message', String(message.id)));
    },

    readRoomLog: async (roomLog: RoomLog): Promise<void> => {
        roomLog.isUnread = false;
        await ApiService.patch(urlJoin(ROOM_LOGS_ENDPOINT, 'read', String(roomLog.id)));
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
            'timeLimit': 48
        });

        const messages: Message[] = [];
        if (messagesData.data) {
            toArray(messagesData.data).forEach((data: any) => {
                messages.push((new Message()).load(data));
            });
        }
        return messages;
    }
};
export default CommunicationService;
