import ApiService from "@/services/api.service";
import { Alert } from "@/entities/Alerts";
import { Daedalus } from "@/entities/Daedalus";
import urlJoin from "url-join";

// @ts-ignore
const DAEDALUS_ALERTS_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "alert");
// @ts-ignore
const DAEDALUS_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "daedaluses");

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

    createDaedalus: async (name: string): Promise<any> => {
        return ApiService.post(DAEDALUS_ENDPOINT + '?XDEBUG_SESSION_START=PHPSTORM', {
            name: name
        });
    }
};
export default DaedalusService;
