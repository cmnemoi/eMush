import ApiService from "@/services/api.service";
import { Alert } from "@/entities/Alerts";
import { Daedalus } from "@/entities/Daedalus";
import urlJoin from "url-join";
import { Minimap } from "@/entities/Minimap";
import { GameConfig } from "@/entities/Config/GameConfig";

// @ts-ignore
const DAEDALUS_ALERTS_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "alert");
// @ts-ignore
const DAEDALUS_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "daedaluses");
// @ts-ignore
const CREATE_DAEDALUS_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "daedaluses/create-daedalus");

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
    }
};
export default DaedalusService;
