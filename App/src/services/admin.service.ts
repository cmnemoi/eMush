import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import store from "@/store";
import { AxiosResponse } from "axios";

const API_URL = import.meta.env.VITE_APP_API_URL as string;

const ADMIN_ENDPOINT = urlJoin(API_URL, "admin");
const STATS_ENDPOINT = urlJoin(API_URL, "statistics");

const AdminService = {
    addNewRoomsToDaedalus: async(daedalusId: integer): Promise<AxiosResponse> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(ADMIN_ENDPOINT + '/add-new-rooms-to-daedalus/' + String(daedalusId));
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    closeAllPlayers: async(): Promise<AxiosResponse> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(ADMIN_ENDPOINT + '/close-all-players');
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    closePlayer: async(playerId: string): Promise<AxiosResponse> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(ADMIN_ENDPOINT + '/close-player/' + playerId);
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    deleteDaedalusDuplicatedAlertElements: async(daedalusId: integer): Promise<AxiosResponse> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(ADMIN_ENDPOINT + '/delete-daedalus-duplicated-alert-elements/' + String(daedalusId));
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    getMaintenanceStatus: async(): Promise<AxiosResponse> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.get(ADMIN_ENDPOINT + '/maintenance');
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    putGameInMaintenance: async(): Promise<AxiosResponse> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(ADMIN_ENDPOINT + '/maintenance');
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    removeGameFromMaintenance: async(): Promise<AxiosResponse> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.delete(ADMIN_ENDPOINT + '/maintenance');
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    sendNeronAnnouncementToAllDaedalusesByLanguage: async(announcement: string, language: string): Promise<AxiosResponse> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(
            ADMIN_ENDPOINT + '/neron-announcement',
            { announcement: announcement, language: language }
        );

        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    sendNeronAnnouncementToTarget: async(announcement: string, id: string): Promise<AxiosResponse> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(
            ADMIN_ENDPOINT + '/neron-announcement-targeted',
            { announcement: announcement, shipId: id }
        );

        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    unlockDaedalus: async(daedalusId: integer): Promise<AxiosResponse> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(urlJoin(ADMIN_ENDPOINT, 'debug', 'unlock-daedalus', String(daedalusId)));
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    deliverStats: async(daedalusId: integer): Promise<AxiosResponse> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(urlJoin(STATS_ENDPOINT, 'deliver', String(daedalusId)));
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    deleteModifiers: async(): Promise<AxiosResponse> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(urlJoin(ADMIN_ENDPOINT, 'delete-modifiers'));
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    createModifiers: async(): Promise<AxiosResponse> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(urlJoin(ADMIN_ENDPOINT, 'create-modifiers'));
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    }

};
export default AdminService;
