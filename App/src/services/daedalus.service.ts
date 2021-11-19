import ApiService from "@/services/api.service";
import { Alert } from "@/entities/Alerts";
import { Daedalus } from "@/entities/Daedalus";
import urlJoin from "url-join";

const DAEDALUS_ALERTS_ENDPOINT = urlJoin((process.env.VUE_APP_API_URL) as string, "alert");

const DaedalusService = {
    loadAlerts: async (daedalus: Daedalus): Promise<Alert[]> => {
        const alertsData = await ApiService.get(DAEDALUS_ALERTS_ENDPOINT + '/' + daedalus.id + '/alerts');

        const alerts: Alert[] = [];
        if (alertsData.data) {
            alertsData.data.forEach((data: Alert) => {
                alerts.push((new Alert()).load(data));
            });
        }
        return alerts;
    }
};
export default DaedalusService;
