import ApiService from "@/services/api.service";
import { RoomLog } from "@/entities/RoomLog";
import urlJoin from "url-join";
import store from "@/store";
import { PlayerInfo } from "@/entities/PlayerInfo";
import { Message } from "@/entities/Message";
import { Channel } from "@/entities/Channel";
import { ModerationViewPlayer } from "@/entities/ModerationViewPlayer";

const API_URL = process.env.VUE_APP_API_URL as string;

const CHANNEL_ENDPOINT = urlJoin(API_URL, "channels");
const MESSAGES_ENDPOINT = urlJoin(API_URL, "messages");
const MODERATION_ENDPOINT = urlJoin(API_URL, "moderation");
const PLAYER_INFO_ENDPOINT = urlJoin(API_URL, "player_infos");
const ROOM_LOG_ENDPOINT = urlJoin(API_URL, "room_logs");

type ChannelScope = "public" | "mush" | "private";

const ModerationService = {
    banUser: async(userId: integer, params: URLSearchParams): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(MODERATION_ENDPOINT + '/ban-user/' + userId+ '?' + params.toString());
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    getChannelMessages: async(channel: Channel,startDate: string, endDate: string, message?: string, author?: string): Promise<Message[]> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const queryParameters = `channel.id=${channel.id}` + (startDate ? `&updatedAt[after]=${startDate}` : '') + (endDate ? `&updatedAt[before]=${endDate}` : '') + (message ? `&message=${message}` : '') + (author ? `&author.characterConfig.characterName=${author}` : '') + '&order[updatedAt]=desc';

        const messages = await ApiService.get(`${MESSAGES_ENDPOINT}?${queryParameters}`).then((response) => {
            return response.data['hydra:member'].map((messageData: object) => {
                return (new Message()).load(messageData);
            });
        });
        store.dispatch('gameConfig/setLoading', { loading: false });

        return messages;
    },
    getModerationViewPlayer: async(playerId: number): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.get(MODERATION_ENDPOINT + '/view-player/' + playerId);
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    getPlayerDaedalusChannelByScope: async(player: ModerationViewPlayer, scope: ChannelScope): Promise<Channel> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const channels = await ApiService.get(`${CHANNEL_ENDPOINT}?daedalusInfo.id=${player.daedalusId}&scope=${scope}`).then((response) => {
            return response.data['hydra:member'].map((channelData: any) => {
                return (new Channel()).load(channelData);
            });
        });
        store.dispatch('gameConfig/setLoading', { loading: false });

        return channels[0];
    },
    getPlayerPrivateChannels: async(player: ModerationViewPlayer): Promise<Channel[]> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const channels = await ApiService.get(`${CHANNEL_ENDPOINT}?participants.participant.id=${player.id}&scope=private`).then((response) => {
            return response.data['hydra:member'].map((channelData: any) => {
                return (new Channel()).load(channelData);
            });
        });

        store.dispatch('gameConfig/setLoading', { loading: false });

        return channels;
    },
    getPlayerInfoList: async(params: Record<string, unknown> | undefined): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });

        const response = await ApiService.get(PLAYER_INFO_ENDPOINT, params);
        response.data['hydra:member'] = response.data['hydra:member'].map((playerInfoData: Record<string, any>) => {
            return (new PlayerInfo()).load(playerInfoData);
        });
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    getPlayerLogs: async(playerId: number, day: integer, cycle: integer | null, content?: string, place?: string): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const queryParameters = `playerInfo.id=${playerId}` + (day ? `&day=${day}` : '') + (cycle ? `&cycle=${cycle}` : '') + (content ? `&log=${content}` : '') + (place ? `&place=${place}` : '');
        const response = await ApiService.get(`${ROOM_LOG_ENDPOINT}?${queryParameters}`);

        const roomLogs: RoomLog[] = [];
        response.data['hydra:member'].forEach((logData: any) => {
            const roomLog = (new RoomLog()).load(logData);
            roomLogs.push(roomLog);
        });

        const days = new Set(roomLogs.map((log) => log.day));

        const logs: Record<string, unknown>[] = [];
        days.forEach((day) => {
            const cycles = new Set(roomLogs.filter((log) => log.day === day).map((log) => log.cycle));
            cycles.forEach((cycle) => {
                const roomLogsForCycle = roomLogs.filter((log) => log.day === day && log.cycle === cycle);
                logs.push({
                    "day": day,
                    "cycle": cycle,
                    "roomLogs": roomLogsForCycle.reverse()
                });         
            });
        });

        store.dispatch('gameConfig/setLoading', { loading: false });

        return { "data": logs };
    },
    editClosedPlayerEndMessage: async(playerId: number, params: URLSearchParams): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(MODERATION_ENDPOINT + '/edit-closed-player-end-message/' + playerId+ '?' + params.toString());
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    hideClosedPlayerEndMessage: async(playerId: number, params: URLSearchParams): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(MODERATION_ENDPOINT + '/hide-closed-player-end-message/' + playerId+ '?' + params.toString());
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    deleteMessage: async(messageId: number, params: URLSearchParams): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(MODERATION_ENDPOINT + '/delete-message/' + messageId+ '?' + params.toString());
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    quarantinePlayer: async(playerId: number, params: URLSearchParams): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(MODERATION_ENDPOINT + '/quarantine-player/' + playerId + '?' + params.toString());
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    suspendSanction: async(sanctionId: number): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.patch(MODERATION_ENDPOINT + '/suspend-sanction/' + sanctionId);
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    removeSanction: async(sanctionId: number): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(MODERATION_ENDPOINT + '/remove-sanction/' + sanctionId);
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
};

export default ModerationService;