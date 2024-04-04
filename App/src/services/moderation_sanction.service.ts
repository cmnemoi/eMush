import ApiService from "@/services/api.service";
import { ModerationSanction } from "@/entities/ModerationSanction";
import urlJoin from "url-join";
import store from "@/store";

const API_URL = process.env.VUE_APP_API_URL as string;

const MODERATION_SANCTION_ENDPOINT = urlJoin(API_URL, "moderation_sanctions");

const ModerationSanctionService = {
    getUserActiveWarnings: async(userId: string): Promise<ModerationSanction[]> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.get(MODERATION_SANCTION_ENDPOINT, {
            params: {
                "user.userId": userId,
                isActive: true,
                moderationAction: 'warning',
                order: {
                    startDate: 'desc'
                }
            }
        });
        
        const sanctions = response.data['hydra:member'].map((sanctionData: object) => {
            return (new ModerationSanction()).load(sanctionData);
        });
        store.dispatch('gameConfig/setLoading', { loading: false });

        return sanctions;
    }
};
export default ModerationSanctionService;