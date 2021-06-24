import ApiService from "@/services/api.service";
import { Alert } from "@/entities/Alerts";
import { Daedalus } from "@/entities/Daedalus";

const DAEDALUS_ALERTS_ENDPOINT = process.env.VUE_APP_API_URL + 'alert';

const DaedalusService = {
    loadAlerts: async (daedalus: Daedalus) => {
        const alertsData = await ApiService.get(DAEDALUS_ALERTS_ENDPOINT + '/' + daedalus.id + '/alerts');

        let alerts: Alert[] = [];
        if (alertsData.data) {
            alertsData.data.forEach((data: any) => {
                alerts.push((new Alert()).load(data));
            });
        }
        return alerts;
    }
};
export default DaedalusService;
