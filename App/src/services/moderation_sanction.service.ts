import ApiService from "@/services/api.service";
import { ModerationSanction } from "@/entities/ModerationSanction";
import urlJoin from "url-join";
import store from "@/store";

const API_URL = import.meta.env.VITE_APP_API_URL as string;

const MODERATION_SANCTION_ENDPOINT = urlJoin(API_URL, "moderation_sanctions");

const ModerationSanctionService = {
    getUserActiveBansAndWarnings: async(userId: integer): Promise<ModerationSanction[]> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.get(
            urlJoin(MODERATION_SANCTION_ENDPOINT, String(userId), 'active-bans-and-warnings')
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
    },
    getPlayerReports: async(playerInfoId: integer): Promise<ModerationSanction[]> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.get(
            urlJoin(MODERATION_SANCTION_ENDPOINT, String(playerInfoId), 'reports')
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
export default ModerationSanctionService;
