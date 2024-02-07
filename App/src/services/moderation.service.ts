import ApiService from "@/services/api.service";
import { RoomLog } from "@/entities/RoomLog";
import urlJoin from "url-join";
import store from "@/store";
import { PlayerInfo } from "@/entities/PlayerInfo";

const API_URL = process.env.VUE_APP_API_URL as string;

const MODERATION_ENDPOINT = urlJoin(API_URL, "moderation");
const PLAYER_INFO_ENDPOINT = urlJoin(API_URL, "player_infos");

const ModerationService = {
    banUser: async(userId: integer): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.patch(MODERATION_ENDPOINT + '/ban-user/' + userId);
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    getModerationViewPlayer: async(playerId: number): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.get(MODERATION_ENDPOINT + '/view-player/' + playerId);
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
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
    getPlayerLogs: async(playerId: number): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.get(MODERATION_ENDPOINT + '/player-logs/' + playerId);

        const logs: Record<string, unknown>[] = [];
        if (response.data) {
            const days = response.data;
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
        store.dispatch('gameConfig/setLoading', { loading: false });

        return { "data": logs };
    },
    quarantinePlayer: async(playerId: number): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.patch(MODERATION_ENDPOINT + '/quarantine-player/' + playerId);
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    unbanUser: async(userId: integer): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.patch(MODERATION_ENDPOINT + '/unban-user/' + userId);
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    }
};

export default ModerationService;