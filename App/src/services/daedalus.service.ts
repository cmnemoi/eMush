import ApiService from "@/services/api.service";
import { Alert } from "@/entities/Alerts";
import { Daedalus } from "@/entities/Daedalus";
import urlJoin from "url-join";
import { Minimap } from "@/entities/Minimap";
import { ClosedDaedalus } from "@/entities/ClosedDaedalus";
import store from "@/store";

// @ts-ignore
const DAEDALUS_ALERTS_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "alert");
// @ts-ignore
const DAEDALUS_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "daedaluses");
// @ts-ignore
const CREATE_DAEDALUS_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "daedaluses/create-daedalus");
// @ts-ignore
const CLOSED_DAEDALUS_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "closed_daedaluses");
// @ts-ignore
const DESTROY_DAEDALUS_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "daedaluses/destroy-daedalus");
// @ts-ignore
const DESTROY_ALL_DAEDALUS_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "daedaluses/destroy-all-daedaluses");

const DaedalusService = {
    loadAlerts: async (daedalus: Daedalus): Promise<Alert[]> => {
        const alertsData = await ApiService.get(DAEDALUS_ALERTS_ENDPOINT + '/' + daedalus.id + '/alerts');

        const alerts: Alert[] = [];
        if (alertsData.data) {
            alertsData.data.forEach((data: any) => {
                alerts.push((new Alert()).load(data));
            });
        }
        return alerts;
    },
    loadMinimap: async (daedalus: Daedalus): Promise<Minimap[]> => {
        const minimapData = await ApiService.get(DAEDALUS_ENDPOINT + '/' + daedalus.id + '/minimap');

        const minimap: Minimap[] = [];
        if (minimapData.data) {

            Object.values(minimapData.data).forEach((data: any) => {
                minimap.push((new Minimap()).load(data));
            });
        }
        return minimap;
    },
    createDaedalus: async (name: string, config: string, language: string): Promise<any> => {
        return ApiService.post(CREATE_DAEDALUS_ENDPOINT, {
            'config' : config,
            'name' : name,
            'language' : language
        });
    },
    loadClosedDaedalus: async (closedDaedalusId: integer): Promise<ClosedDaedalus | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const closedDaedalusData = await ApiService.get(CLOSED_DAEDALUS_ENDPOINT + '/' + closedDaedalusId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let closedDaedalus = null;
        if (closedDaedalusData.data) {
            closedDaedalus = (new ClosedDaedalus()).load(closedDaedalusData.data);
        }

        return closedDaedalus;
    },
    destroyDaedalus: async (daedalusId: integer): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = ApiService.post(DESTROY_DAEDALUS_ENDPOINT + '/' + daedalusId);
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    destroyAllDaedaluses: async (): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = ApiService.post(DESTROY_ALL_DAEDALUS_ENDPOINT);
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    }
};
export default DaedalusService;
