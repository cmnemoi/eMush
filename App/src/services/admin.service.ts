import ApiService from "@/services/api.service";
import { PlayerInfo } from "@/entities/PlayerInfo";
import urlJoin from "url-join";
import store from "@/store";

// @ts-ignore
const ADMIN_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "admin");
// @ts-ignore
const PLAYER_INFO_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "player_infos");

const AdminService = {
    addNewRoomsToDaedalus: async(daedalusId: integer): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(ADMIN_ENDPOINT + '/add-new-rooms-to-daedalus/' + String(daedalusId));
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    closeAllPlayers: async(): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(ADMIN_ENDPOINT + '/close-all-players');
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    closePlayer: async(playerId: string): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(ADMIN_ENDPOINT + '/close-player/' + playerId);
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    deleteDaedalusDuplicatedAlertElements: async(daedalusId: integer): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(ADMIN_ENDPOINT + '/delete-daedalus-duplicated-alert-elements/' + String(daedalusId));
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    getMaintenanceStatus: async(): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.get(ADMIN_ENDPOINT + '/maintenance');
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    putGameInMaintenance: async(): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(ADMIN_ENDPOINT + '/maintenance');
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    removeGameFromMaintenance: async(): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.delete(ADMIN_ENDPOINT + '/maintenance');
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    sendNeronAnnouncementToAllDaedalusesByLanguage: async(announcement: string, language: string): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(
            ADMIN_ENDPOINT + '/neron-announcement',
            { announcement: announcement, language: language }
        );

        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    }

};
export default AdminService;
