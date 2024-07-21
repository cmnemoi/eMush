import ApiService from "@/services/api.service";
import { RoomLog } from "@/entities/RoomLog";
import urlJoin from "url-join";
import store from "@/store";
import { PlayerInfo } from "@/entities/PlayerInfo";
import { Message } from "@/entities/Message";
import { Channel } from "@/entities/Channel";
import { ModerationViewPlayer } from "@/entities/ModerationViewPlayer";
import { Player } from "@/entities/Player";
import { ModerationSanction } from "@/entities/ModerationSanction";

const API_URL = import.meta.env.VITE_APP_API_URL as string;

const CHANNEL_ENDPOINT = urlJoin(API_URL, "channels");
const MESSAGES_ENDPOINT = urlJoin(API_URL, "messages");
const MODERATION_ENDPOINT = urlJoin(API_URL, "moderation");
const PLAYER_INFO_ENDPOINT = urlJoin(API_URL, "player_infos");
const ROOM_LOG_ENDPOINT = urlJoin(API_URL, "room_logs");

type ChannelScope = "public" | "mush" | "private";

function toArray(data: any): any[] {
    return Array.isArray(data) ? data : Object.values(data);
}

const ModerationService = {
    applySanctionToPlayer: async(player: Player, sanctionName: string, params: URLSearchParams): Promise<any> => {
        if (sanctionName === 'quarantine_player' || sanctionName === 'quarantine_ban') {
            ModerationService.quarantinePlayer(player.id, params)
                .then(() => {
                    this.loadData();
                })
                .catch((error) => {
                    console.error(error);
                });
        }
        if (sanctionName === 'ban_user' || sanctionName === 'quarantine_ban') {
            ModerationService.banUser(player.user.id, params)
                .then(() => {
                    this.loadData();
                })
                .catch((error) => {
                    console.error(error);
                });
        }
        if (sanctionName === 'warning') {
            ModerationService.warnUser(player.user.id, params)
                .then(() => {
                    this.loadData();
                })
                .catch((error) => {
                    console.error(error);
                });
        }
    },
    banUser: async(userId: integer, params: URLSearchParams): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(MODERATION_ENDPOINT + '/ban-user/' + userId+ '?' + params.toString());
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    getChannelMessages: async(channel: Channel, filters: { startDate: string, endDate: string, messageContent?: string, author?: string } ): Promise<Message[]> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const queryParameters = `pagination=false&channel.id=${channel.id}`
            + (filters.startDate ? `&updatedAt[after]=${filters.startDate}` : '')
            + (filters.endDate ? `&updatedAt[before]=${filters.endDate}` : '')
            + (filters.messageContent ? `&message=${filters.messageContent}` : '')
            + (filters.author ? `&author.characterConfig.characterName=${filters.author}` : ''
            ) + '&order[updatedAt]=desc';

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
        const playerInfos = [];
        for (const data of response.data['hydra:member']) {
            if (data) {
                playerInfos.push(new PlayerInfo().load(data));
            }
        }
        response.data['hydra:member'] = playerInfos;

        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    getPlayerLogs: async(playerId: number, day: integer, cycle: integer | null, content?: string, place?: string): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const queryParameters = `pagination=false&playerInfo.id=${playerId}`
            + (day ? `&day=${day}` : '')
            + (cycle ? `&cycle=${cycle}` : '')
            + (content ? `&log=${content}` : '')
            + (place ? `&place=${place}` : ''
            );
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
    warnUser: async(userId: number, params: URLSearchParams): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(MODERATION_ENDPOINT + '/warn-user/' + userId + '?' + params.toString());
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
    reportClosedPlayer: async(playerId: number, params: URLSearchParams): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(MODERATION_ENDPOINT + '/report-closed-player/' + playerId + '?' + params.toString());
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    reportMessage: async(messageId: number, params: URLSearchParams): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(MODERATION_ENDPOINT + '/report-message/' + messageId + '?' + params.toString());
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    reportLog: async(logId: number, params: URLSearchParams): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(MODERATION_ENDPOINT + '/report-log/' + logId + '?' + params.toString());
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    loadReportablePlayers: async (message: Message): Promise<Player[]> => {
        const playersData = await ApiService.get(MODERATION_ENDPOINT + '/' + message.id + '/reportable');

        const players:Player[] = [];
        if (playersData.data) {
            toArray(playersData.data).forEach((data: any) => {
                players.push((new Player()).load(data));
            });
        }
        return players;
    },
    archiveReport: async(sanctionId: number, params: URLSearchParams): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.patch(MODERATION_ENDPOINT + '/archive-report/' + sanctionId + '?' + params.toString());
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    getUserActiveBansAndWarnings: async(userId: integer): Promise<ModerationSanction[]> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.get(
            urlJoin(MODERATION_ENDPOINT, String(userId), 'active-bans-and-warnings')
        ).then((response) => {
            return response.data;
        }).catch(async (error) => {
            console.error(error);
            await store.dispatch('error/setError', { error: error });
            await store.dispatch('gameConfig/setLoading', { loading: false });
            return [];
        });

        const sanctions = response.map((sanctionData: any) => {
            return (new ModerationSanction()).load(sanctionData);
        });
        store.dispatch('gameConfig/setLoading', { loading: false });

        return sanctions;
    }
};

export default ModerationService;
