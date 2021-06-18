import ApiService from "@/services/api.service";
import { Alert } from "@/entities/Alerts";
import urlJoin from "url-join";

const DAEDALUS_ALERTS_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "alert");

const DaedalusService = {
    loadAlerts: async (daedalus) => {
        const alertsData = await ApiService.get(urlJoin(DAEDALUS_ALERTS_ENDPOINT, daedalus.id.toString(), "alerts"));

        let alerts = [];
        if (alertsData.data) {
            alertsData.data.forEach((data) => {
                alerts.push((new Alert()).load(data));
            });
        }
        return alerts;
    }
};
export default DaedalusService;
